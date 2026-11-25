<?php
namespace App\Http\Controllers;
use App\Models\{Customer,Product};
use App\Services\{LoyaltyService,PackageAccess,PromotionService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PremiumCheckoutQuoteController extends Controller {
    public function __invoke(Request $request,LoyaltyService $loyalty,PromotionService $promotions) {
        $business=app(PackageAccess::class)->currentBusiness();
        abort_unless(app(PackageAccess::class)->allows('customer_loyalty',$business) || app(PackageAccess::class)->allows('promotions',$business),403);
        $data=$request->validate(['products'=>'required|array|min:1|max:500','products.*.product_id'=>'required|integer','products.*.quantity'=>'required|integer|min:1|max:1000000','customer_name'=>'nullable|string|max:100','loyalty_customer_id'=>'nullable|integer','redeem_points'=>'nullable|integer|min:0|max:100000000','voucher_code'=>'nullable|string|max:50','payment_method'=>'required|in:cash,qris,va']);
        return DB::transaction(function()use($data,$business,$loyalty,$promotions){
            $subtotal=0;
            $products=Product::where('business_id',$business->id)->whereIn('id',array_column($data['products'],'product_id'))->get()->keyBy('id');
            foreach($data['products'] as $line){abort_unless(isset($products[$line['product_id']]),404);$subtotal+=(float)$products[$line['product_id']]->price*$line['quantity'];}
            $customer=filled($data['loyalty_customer_id']??null) ? Customer::where('business_id',$business->id)->findOrFail($data['loyalty_customer_id']) : Customer::where('name',trim($data['customer_name']??''))->first();
            $customer ??= (new Customer)->forceFill(['business_id'=>$business->id,'id'=>0]);
            $points=(int)($data['redeem_points']??0);
            if($points && (empty($data['loyalty_customer_id']) || $data['payment_method']!=='cash'))throw \Illuminate\Validation\ValidationException::withMessages(['redeem_points'=>'Pilih akun customer dan pembayaran tunai untuk redeem.']);
            [, $promo]=$promotions->quote($business,$customer,$subtotal,$data['voucher_code']??null);
            $redeem=$loyalty->quote($business,$customer,$points,$subtotal-$promo);
            return response()->json(['subtotal'=>$subtotal,'promotion_discount'=>$promo,'loyalty_discount'=>$redeem,'total'=>round($subtotal-$promo-$redeem,2)]);
        });
    }
}
