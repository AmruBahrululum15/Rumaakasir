<?php
namespace App\Http\Controllers;
use App\Models\{Business,BusinessCustomer,Customer,CustomerAccount,CustomerLinkInvitation};
use App\Services\{CustomerIdentity,CustomerPortal};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerLinkController extends Controller
{
    private function matches(Customer $customer,CustomerAccount $account):bool
    {
        $phone=CustomerIdentity::phone($customer->phone);
        return ($phone && $phone===CustomerIdentity::phone($account->phone))
            || (filter_var($customer->address,FILTER_VALIDATE_EMAIL) && strtolower(trim($customer->address))===strtolower($account->email));
    }
    public function invite(Request $request,PlatformCustomerController $access)
    {
        $access->authorizeStaff($request);abort_unless($request->user()->can('customers.edit'),403);
        $data=$request->validate(['customer_id'=>'required|integer','email'=>'required|email|max:190']);
        $business=$request->user()->business;$token=Str::random(64);
        $invitation=DB::transaction(function()use($business,$data,$request,$token){
            $business=Business::whereKey($business->id)->lockForUpdate()->firstOrFail();app(CustomerPortal::class)->requireActive($business);
            $customer=Customer::where('business_id',$business->id)->findOrFail($data['customer_id']);
            $account=CustomerAccount::whereRaw('LOWER(email) = ?',[strtolower($data['email'])])->where('status','active')->first();
            if(!$account || !$this->matches($customer,$account))throw ValidationException::withMessages(['email'=>'Akun aktif dan kontak pelanggan yang cocok diperlukan.']);
            if(BusinessCustomer::where('business_id',$business->id)->where(fn($q)=>$q->where('customer_id',$customer->id)->orWhere('customer_account_id',$account->id))->exists())throw ValidationException::withMessages(['customer_id'=>'Pelanggan atau akun sudah terhubung. Tidak dilakukan penggabungan otomatis.']);
            return CustomerLinkInvitation::create(['business_id'=>$business->id,'customer_id'=>$customer->id,'customer_account_id'=>$account->id,'requested_by'=>$request->user()->id,'email'=>$account->email,'token_hash'=>hash('sha256',$token),'expires_at'=>now()->addHour()]);
        });
        try{CustomerAccount::findOrFail($invitation->customer_account_id)->notify(new \App\Notifications\CustomerLinkConfirmation($token,$business->name));}
        catch(\Throwable $error){\Log::warning('Customer link email failed',['invitation_id'=>$invitation->id,'exception'=>get_class($error)]);return back()->withErrors(['email'=>'Email konfirmasi belum berhasil dikirim. Tidak ada pelanggan yang dihubungkan.']);}
        return back()->with('success','Email konfirmasi dikirim. Pengaitan menunggu persetujuan pelanggan.');
    }
    private function invitation(string $token):CustomerLinkInvitation
    {
        abort_unless(strlen($token)===64,404);
        $invitation=CustomerLinkInvitation::where('token_hash',hash('sha256',$token))->where('customer_account_id',auth('customer')->id())->firstOrFail();
        abort_unless(!$invitation->accepted_at && $invitation->expires_at->isFuture(),410,'Tautan sudah digunakan atau kedaluwarsa.');
        return $invitation;
    }
    public function show(string $token)
    {
        $invitation=$this->invitation($token);
        return view('portal.confirm-link',compact('invitation','token'));
    }
    public function accept(Request $request,string $token)
    {
        $request->validate(['confirm'=>'accepted']);$initial=$this->invitation($token);
        DB::transaction(function()use($initial,$token){
            $business=Business::whereKey($initial->business_id)->lockForUpdate()->firstOrFail();app(CustomerPortal::class)->requireActive($business);
            $invitation=CustomerLinkInvitation::whereKey($initial->id)->lockForUpdate()->firstOrFail();
            abort_unless(!$invitation->accepted_at && $invitation->expires_at->isFuture(),410);
            $account=auth('customer')->user();abort_unless($account->email===$invitation->email,403);
            $customer=Customer::withoutGlobalScopes()->where('business_id',$business->id)->findOrFail($invitation->customer_id);
            abort_unless($this->matches($customer,$account),422);
            if(BusinessCustomer::where('business_id',$business->id)->where(fn($q)=>$q->where('customer_id',$customer->id)->orWhere('customer_account_id',$account->id))->exists())throw ValidationException::withMessages(['confirm'=>'Akun atau pelanggan sudah terhubung. Data tidak digabung.']);
            BusinessCustomer::create(['business_id'=>$business->id,'customer_account_id'=>$account->id,'customer_id'=>$customer->id,'joined_at'=>now(),'last_transaction_at'=>DB::table('sales')->where('business_id',$business->id)->where('customer_id',$customer->id)->where('payment_status','PAID')->max('created_at'),'status'=>'active']);
            $invitation->update(['accepted_at'=>now()]);
        });
        return redirect()->route('customer.loyalty')->with('success','Data pelanggan toko berhasil dihubungkan.');
    }
}
