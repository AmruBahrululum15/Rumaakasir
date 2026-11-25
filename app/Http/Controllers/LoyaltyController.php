<?php
namespace App\Http\Controllers;
use App\Models\{Customer,CustomerLoyaltyPoint,Setting};
use App\Services\{LoyaltyService,PackageAccess};
use Illuminate\Http\Request;
class LoyaltyController extends Controller {
    public function index(LoyaltyService $service) {
        $business=app(PackageAccess::class)->currentBusiness();
        return view('premium.loyalty',['settings'=>$service->settings($business),'customers'=>Customer::orderBy('name')->paginate(30)]);
    }
    public function update(Request $request) {
        $data=$request->validate(['loyalty_enabled'=>'required|boolean','spend_per_point'=>'required|integer|min:1|max:1000000000','point_value'=>'required|integer|min:1|max:1000000','minimum_redeem_points'=>'required|integer|min:1|max:1000000']);
        Setting::setValue('loyalty_configuration',json_encode($data));
        return back()->with('success','Pengaturan loyalty disimpan.');
    }
    public function show(Customer $customer,LoyaltyService $service) {
        return view('premium.loyalty-history',['customer'=>$customer,'balance'=>$service->balance($customer->business_id,$customer->id),'entries'=>CustomerLoyaltyPoint::where('customer_id',$customer->id)->orderByDesc('id')->paginate(30)]);
    }
}
