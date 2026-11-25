<?php
namespace App\Http\Controllers;
use App\Models\{Booking,Sale,BusinessCustomer};
use App\Services\{NearbyStores,LoyaltyService};
class CustomerDashboardController extends Controller {
    public function index(){
        $account=auth('customer')->user();
        $bookings=Booking::withoutGlobalScopes()->with('business')->where('customer_account_id',$account->id)->whereIn('status',['pending','confirmed'])->where('starts_at','>=',now())->orderBy('starts_at')->limit(3)->get();
        $sales=Sale::withoutGlobalScopes()->with('business')->where('customer_account_id',$account->id)->latest()->limit(3)->get();
        $loyalty=BusinessCustomer::with('business')->where('customer_account_id',$account->id)->limit(4)->get();
        foreach($loyalty as $m)$m->points=app(LoyaltyService::class)->balance($m->business_id,$m->customer_id);
        $stores=app(NearbyStores::class)->search()->take(4);
        $city=$account->addresses()->where('is_default',true)->value('city');
        return view('portal.dashboard',compact('account','bookings','sales','loyalty','stores','city'));
    }
}
