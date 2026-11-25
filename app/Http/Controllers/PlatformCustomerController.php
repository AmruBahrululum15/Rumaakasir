<?php
namespace App\Http\Controllers;
use App\Models\{BusinessCustomer,CustomerAccount,Conversation};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlatformCustomerController extends Controller
{
    private function query(Request $request,?int $businessId=null)
    {
        $data=$request->validate(['search'=>'nullable|string|max:120','status'=>'nullable|in:active,inactive,suspended']);
        $query=CustomerAccount::select('customer_accounts.id','name','email','phone','status','created_at','last_login_at')
            ->when($businessId,fn($q)=>$q->whereHas('memberships',fn($m)=>$m->where('business_id',$businessId)))
            ->when($data['search']??null,fn($q,$v)=>$q->where(fn($sub)=>$sub->where('name','like',"%{$v}%")->orWhere('email','like',"%{$v}%")->orWhere('phone','like',"%{$v}%")))
            ->when($data['status']??null,fn($q,$v)=>$q->where('status',$v))
            ->withCount(['memberships'=>fn($q)=>$q->when($businessId,fn($q)=>$q->where('business_id',$businessId)),
                'conversations'=>fn($q)=>$q->when($businessId,fn($q)=>$q->where('business_id',$businessId))]);
        foreach(['sales'=>'orders_count','bookings'=>'bookings_count'] as $table=>$alias){
            $query->addSelect([$alias=>DB::table($table)->selectRaw('COUNT(*)')->when($businessId,fn($q)=>$q->where($table.'.business_id',$businessId))
                ->where(function($q)use($table){$q->whereColumn($table.'.customer_account_id','customer_accounts.id')
                    ->orWhereExists(fn($m)=>$m->selectRaw('1')->from('business_customers')->whereColumn('business_customers.customer_account_id','customer_accounts.id')->whereColumn('business_customers.customer_id',$table.'.customer_id')->whereColumn('business_customers.business_id',$table.'.business_id'));})]);
        }
        return $query->orderByDesc('customer_accounts.id');
    }
    public function index(Request $request)
    {
        return view('portal.platform-customers',['accounts'=>$this->query($request)->paginate(25)->withQueryString(),'staff'=>false]);
    }
    public function show(CustomerAccount $account)
    {
        // Deliberately never load messages, message previews or notification bodies.
        $memberships=BusinessCustomer::with('business:id,name')->where('customer_account_id',$account->id)->get();
        $conversations=Conversation::select('id','business_id','last_message_at','status')->with('business:id,name')
            ->where('customer_account_id',$account->id)->orderByDesc('last_message_at')->paginate(25);
        return view('portal.platform-customer',compact('account','memberships','conversations'));
    }
    public function staff(Request $request)
    {
        $this->authorizeStaff($request);
        $businessId=(int)$request->user()->business_id;
        return view('portal.platform-customers',['accounts'=>$this->query($request,$businessId)->paginate(25)->withQueryString(),'staff'=>true]);
    }
    public function authorizeStaff(Request $request):void
    {
        abort_unless(!$request->user()->hasRole('Super Admin') && $request->user()->hasAnyRole(['Atasan','Admin']) && $request->user()->can('customers.view'),403);
        app(\App\Services\CustomerPortal::class)->requireActive(\App\Models\Business::findOrFail($request->user()->business_id));
    }
}
