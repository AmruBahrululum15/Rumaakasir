<?php
namespace App\Http\Controllers;
use App\Models\{Booking,BookingResource,Business,BusinessService,Customer,User};
use App\Services\{BookingService,CustomerPortal,ReservationSchedule};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class PublicReservationController extends Controller {
    private function active(Business $business):void{app(CustomerPortal::class)->requireActive($business,'customer_booking');abort_unless(app(ReservationSchedule::class)->settings($business)['enabled'],403,'Reservasi toko dinonaktifkan.');}
    public function form(Request $r,Business $business){
        $this->active($business);$token=Str::random(48);$keys=$r->session()->get('reservation_requests',[]);$keys=array_filter($keys,fn($v)=>$v['expires']>time());if(count($keys)>=20)array_shift($keys);$keys[hash('sha256',$token)]=['business'=>$business->id,'expires'=>time()+7200];$r->session()->put('reservation_requests',$keys);
        return view('portal.reserve',['business'=>$business,'services'=>BusinessService::withoutGlobalScopes()->where('business_id',$business->id)->where('kind','appointment')->where('is_active',true)->get(),'staff'=>User::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->select('id','name')->get(),'resources'=>BookingResource::withoutGlobalScopes()->where('business_id',$business->id)->where('is_active',true)->get(),'settings'=>app(ReservationSchedule::class)->settings($business),'requestToken'=>$token]);
    }
    public function slots(Request $r,Business $business,ReservationSchedule $schedule){$this->active($business);$d=$r->validate(['service_id'=>'required|integer','date'=>'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:'.now()->addYear()->toDateString(),'staff_id'=>'nullable|integer','resource_id'=>'nullable|integer','capacity_units'=>'nullable|integer|min:1|max:1000']);return response()->json(['slots'=>$schedule->slots($business,$d['service_id'],$d['date'],$d['staff_id']??null,$d['resource_id']??null,$d['capacity_units']??1)])->header('Cache-Control','no-store');}
    public function store(Request $r,Business $business,CustomerPortal $portal,BookingService $service){
        $this->active($business);$d=$r->validate(['request_token'=>'required|string|size:48','customer_name'=>'required|string|max:120','customer_phone'=>'required|string|max:30|regex:/^\+?[0-9\s()-]{8,30}$/','customer_email'=>'nullable|email|max:190','service_id'=>'required|integer','staff_id'=>'nullable|integer','resource_id'=>'nullable|integer','capacity_units'=>'nullable|integer|min:1|max:1000','booking_date'=>'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:'.now()->addYear()->toDateString(),'start_time'=>'required|date_format:H:i','notes'=>'nullable|string|max:1000']);
        $hash=hash('sha256',$d['request_token']);$key=$r->session()->get('reservation_requests',[])[$hash]??null;abort_unless($key && $key['business']==$business->id && $key['expires']>time(),419);
        DB::transaction(function()use($business,$portal,$service,$d,$hash){
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();if(Booking::withoutGlobalScopes()->where('business_id',$business->id)->where('public_token_hash',$hash)->exists())return;
            $portal->within($business,function()use($business,$portal,$service,$d,$hash){
                $account=auth('customer')->user();$customer=$account?$portal->join($business,$account)->customer_id:Customer::create(['name'=>$d['customer_name'],'phone'=>$d['customer_phone']])->id;
                $booking=$service->saveForBusiness($business,$d+['customer_id'=>$customer]);$booking->forceFill(['public_token_hash'=>$hash,'customer_account_id'=>$account?->id])->save();
            });
        });return redirect()->route('reservations.status',[$business->slug,$d['request_token']]);
    }
    public function status(Request $r,Business $business,string $token){
        $b=Booking::withoutGlobalScopes()->where('business_id',$business->id)->where('public_token_hash',hash('sha256',$token))->firstOrFail();
        if($r->expectsJson())return response()->json(['status_code'=>$b->status,'status'=>BookingService::STATUSES[$b->status]??$b->status,'payment_status'=>$b->payment_status,'paid'=>$b->paid,'remaining'=>max(0,(float)$b->total-(float)$b->paid)])->header('Cache-Control','private, no-store');
        return response()->view('portal.reservation-status',['booking'=>$b,'business'=>$business,'statuses'=>BookingService::STATUSES])->header('Cache-Control','private, no-store')->header('Referrer-Policy','no-referrer')->header('X-Robots-Tag','noindex, nofollow');
    }
    public function ownStatus(Request $r,int $booking){
        $b=Booking::withoutGlobalScopes()->where('customer_account_id',auth('customer')->id())->findOrFail($booking);
        if($r->expectsJson())return response()->json(['status_code'=>$b->status,'status'=>BookingService::STATUSES[$b->status]??$b->status,'payment_status'=>$b->payment_status,'paid'=>$b->paid,'remaining'=>max(0,(float)$b->total-(float)$b->paid)])->header('Cache-Control','private, no-store');
        return response()->view('portal.reservation-status',['booking'=>$b,'business'=>$b->business,'statuses'=>BookingService::STATUSES])->header('Cache-Control','private, no-store');
    }
}

