<?php
namespace App\Http\Controllers;
use App\Models\{Business,BusinessCustomer,BusinessService,Booking,Customer,CustomerLoyaltyPoint,LaundryOrder,Product,Promotion,Sale,User};
use App\Services\{BookingService,CustomerPortal,LoyaltyService,PackageAccess};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPortalController extends Controller
{
    public function product(Business $business,int $product,CustomerPortal $portal)
    {
        $portal->requireActive($business);
        $product=Product::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->findOrFail($product);
        $product->setRelation('category',\App\Models\Category::withoutGlobalScopes()->where('business_id',$business->id)->find($product->category_id));
        return view('portal.product',compact('business','product'));
    }
    public function directory(Request $r, CustomerPortal $portal)
    {
        $data=$r->validate(['q'=>'nullable|string|max:100','type'=>'nullable|string|max:40','city'=>'nullable|string|max:100','page'=>'nullable|integer|min:1','latitude'=>'nullable|required_with:longitude|numeric|between:-90,90','longitude'=>'nullable|required_with:latitude|numeric|between:-180,180','radius'=>'nullable|in:1,5,10,25']);
        $nearby=app(\App\Services\NearbyStores::class);
        $stores=$nearby->search($data);
        $markers=$nearby->markers($stores);
        $stores=new \Illuminate\Pagination\LengthAwarePaginator($stores->forPage($data['page']??1,12)->values(),$stores->count(),12,$data['page']??1,['path'=>$r->url(),'query'=>$r->query()]);
        return view('portal.directory',compact('stores','markers'));
    }
    public function store(Request $r, Business $business, CustomerPortal $portal)
    {
        if (!$portal->active($business)) return response()->view('portal.unavailable',compact('business'),403);
        $r->validate(['q'=>'nullable|string|max:100','category'=>'nullable|integer']);
        return $portal->within($business,function()use($business,$r,$portal){
            $products=Product::where('business_id',$business->id)->where('is_active',true)->when($r->q,fn($q,$v)=>$q->where('name','like','%'.$v.'%'))->when($r->category,fn($q,$v)=>$q->where('category_id',$v))->orderBy('name')->paginate(24)->withQueryString();
            $services=BusinessService::where('business_id',$business->id)->where('is_active',true)->get();
            $staff=User::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->select('id','name')->get();
            $categories=\App\Models\Category::where('business_id',$business->id)->get();
            $membership=auth('customer')->check()?BusinessCustomer::where('business_id',$business->id)->where('customer_account_id',auth('customer')->id())->first():null;
            $points=$membership?app(LoyaltyService::class)->balance($business->id,$membership->customer_id):0;
            $tables=\App\Models\Table::where('business_id',$business->id)->where('is_active',true)->get();
            $markers = app(\App\Services\NearbyStores::class)->markers(collect([$business]));
            return view('portal.store',compact('business','products','services','staff','categories','points','tables','portal','markers'));
        });
    }
    public function book(Request $r,Business $business,CustomerPortal $portal,BookingService $booking)
    {
        $portal->requireActive($business,'customer_booking');
        $d=$r->validate(['service_id'=>'required|integer','staff_id'=>'nullable|integer','resource_id'=>'nullable|integer','capacity_units'=>'nullable|integer|min:1|max:1000','booking_date'=>'required|date_format:Y-m-d','start_time'=>'required|date_format:H:i','notes'=>'nullable|string|max:1000']);
        DB::transaction(function()use($business,$portal,$booking,$d){
            $business=Business::whereKey($business->id)->lockForUpdate()->firstOrFail(); $portal->requireActive($business,'customer_booking');
            $member=$portal->join($business,auth('customer')->user());
            $portal->within($business,function()use($business,$booking,$d,$member){
                $record=$booking->saveForBusiness($business,$d+['customer_id'=>$member->customer_id]);
                $record->forceFill(['customer_account_id'=>auth('customer')->id()])->save();
            });
        });
        return redirect()->route('customer.bookings')->with('success','Reservasi dikirim. Toko akan mengonfirmasi jadwal Anda.');
    }
    public function cancel(Request $r,int $booking)
    {
        DB::transaction(function()use($booking){
            $record=Booking::withoutGlobalScopes()->where('customer_account_id',auth('customer')->id())->findOrFail($booking);
            Business::whereKey($record->business_id)->lockForUpdate()->firstOrFail();
            $record=Booking::withoutGlobalScopes()->where('customer_account_id',auth('customer')->id())->lockForUpdate()->findOrFail($booking);
            abort_unless(in_array($record->status,['pending','awaiting_payment','confirmed']) && $record->starts_at->isFuture(),422);
            $record->update(['status'=>'cancelled']);
            DB::afterCommit(fn()=>app(\App\Services\BookingNotifications::class)->send($record->id,'cancelled'));
        }); return back()->with('success','Reservasi dibatalkan.');
    }
    public function history(Request $r)
    {
        $section=$r->route()->defaults['section']??'dashboard';
        if($section==='dashboard')return app(\App\Http\Controllers\CustomerDashboardController::class)->index();
        $members=BusinessCustomer::with('business')->where('customer_account_id',auth('customer')->id())->get();
        $bookings=Booking::withoutGlobalScopes()->with(['business','staff:id,name'])->where('customer_account_id',auth('customer')->id())->orderByDesc('starts_at')->paginate(20);
        $sales=Sale::withoutGlobalScopes()->with('business')->where('customer_account_id',auth('customer')->id())->latest()->paginate(20);
        $laundry=LaundryOrder::withoutGlobalScopes()->with('business')->whereIn('customer_id',$members->pluck('customer_id'))->whereIn('business_id',$members->pluck('business_id'))->latest()->limit(50)->get();
        $loyalty=$members->map(function($member){
            $member->points=app(LoyaltyService::class)->balance($member->business_id,$member->customer_id);
            $member->configuration=app(LoyaltyService::class)->settings($member->business);
            $member->history=CustomerLoyaltyPoint::withoutGlobalScopes()->where('business_id',$member->business_id)->where('customer_id',$member->customer_id)->latest('id')->limit(30)->get();
            return $member;
        });
        $vouchers=collect();
        foreach($members as $member) {
            if(!app(CustomerPortal::class)->active($member->business,'customer_vouchers')) continue;
            $eligible=Promotion::withoutGlobalScopes()->where('business_id',$member->business_id)->where('is_active',true)->whereNotNull('code')->where('start_at','<=',now())->where('end_at','>=',now())->get()->filter(function($promo)use($member){
                $usage=DB::table('promotion_usages')->where('promotion_usages.business_id',$member->business_id)->where('promotion_id',$promo->id)->join('sales',fn($j)=>$j->on('sales.id','=','promotion_usages.sale_id')->where('sales.business_id',$member->business_id))->whereNotIn('sales.payment_status',['FAILED','CANCELLED','EXPIRED','VOIDED','REFUNDED']);
                return ($promo->usage_limit===null || (clone $usage)->count()<$promo->usage_limit) && ($promo->per_customer_limit===null || $usage->where('promotion_usages.customer_id',$member->customer_id)->count()<$promo->per_customer_limit);
            });
            foreach($eligible as $promo){$promo->store_name=$member->business->name;$vouchers->push($promo);}
        }
        return view('portal.history',compact('section','members','bookings','sales','laundry','loyalty','vouchers'));
    }
    public function join(Business $business,CustomerPortal $portal)
    {
        $portal->requireActive($business);$portal->join($business,auth('customer')->user());return back()->with('success','Toko ditambahkan ke akun Anda.');
    }
    public function summary()
    {
        $accounts=\App\Models\CustomerAccount::count();
        $stores=Business::whereNotNull('business_type')->get()->filter(fn($b)=>app(CustomerPortal::class)->active($b))->count();
        $memberships=BusinessCustomer::count();$conversations=\App\Models\Conversation::count();
        $reservationStats=['Reservasi hari ini'=>Booking::withoutGlobalScopes()->whereDate('starts_at',now()->toDateString())->count(),'Reservasi seluruh tenant'=>Booking::withoutGlobalScopes()->count()];
        return view('portal.summary',compact('accounts','stores','memberships','conversations','reservationStats'));
    }
}
