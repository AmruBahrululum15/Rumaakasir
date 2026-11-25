<?php
namespace App\Services;
use App\Models\{Business,Customer,CustomerAccount,Product,Sale,SaleDetail};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PortalOrderService
{
    public const TYPES=['retail','minimarket','fashion','cafe','restaurant','kedai','lainnya'];
    public function create(Business $business,CustomerAccount $account,array $cart,string $token,?string $voucher,string $method='cash'): Sale
    {
        $portal=app(CustomerPortal::class);
        return DB::transaction(function()use($business,$account,$cart,$token,$voucher,$portal,$method){
            $business=Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $portal->requireActive($business);abort_unless(in_array($business->business_type,self::TYPES),403);
            $existing=DB::table('portal_checkouts')->where('id',$token)->first();
            if($existing){abort_unless((int)$existing->business_id===$business->id && (int)$existing->customer_account_id===$account->id,404);return Sale::withoutGlobalScopes()->where('business_id',$business->id)->findOrFail($existing->sale_id);}
            if(!$cart || count($cart)>50)throw ValidationException::withMessages(['cart'=>'Keranjang kosong atau terlalu banyak item.']);
            $member=$portal->join($business,$account);
            return $portal->within($business,function()use($business,$account,$cart,$token,$voucher,$member,$method){
                $products=Product::where('business_id',$business->id)->where('is_active',true)->whereIn('id',array_keys($cart))->orderBy('id')->lockForUpdate()->get();
                abort_unless($products->count()===count($cart),422,'Produk tidak tersedia.');$total=0;
                foreach($products as $product){
                    $qty=$cart[$product->id];$allocated=DB::table('outlet_stocks')->where('business_id',$business->id)->where('product_id',$product->id)->sum('quantity');
                    if(!is_int($qty)||$qty<1||$qty>100||$qty>$product->stock-$allocated)throw ValidationException::withMessages(['cart'=>'Stok '.$product->name.' tidak mencukupi.']);
                    $total+=round((float)$product->price*$qty,2);
                }
                $customer=Customer::where('business_id',$business->id)->findOrFail($member->customer_id);
                [$promotion,$discount]=app(PromotionService::class)->quote($business,$customer,$total,$voucher);
                $sale=new Sale(['code'=>'WEB-'.Str::ulid(),'sale_date'=>now()->toDateString(),'total'=>$total-$discount,'paid'=>0,'change'=>0,'customer_id'=>$customer->id,'user_id'=>null,'payment_method'=>'cash','payment_status'=>'CASH_PENDING','order_source'=>'customer_portal','promotion_discount'=>$discount,'notes'=>"ORDER_TYPE=CUSTOMER_PORTAL\nSTOCK_RESERVED=1"]);
                $sale->customer_account_id=$account->id;$sale->payment_method=$method;$sale->payment_status=$method==='cash'?'CASH_PENDING':'PENDING';$sale->order_status='pending';$sale->save();
                app(CustomerPaymentMethods::class)->snapshot($sale,$business);
                foreach($products as $product){$qty=$cart[$product->id];SaleDetail::create(['sale_id'=>$sale->id,'product_id'=>$product->id,'quantity'=>$qty,'price'=>$product->price,'subtotal'=>round((float)$product->price*$qty,2)]);$product->stock-=$qty;$product->save();}
                app(PromotionService::class)->record($promotion,$sale,$discount);
                DB::table('portal_checkouts')->insert(['id'=>$token,'business_id'=>$business->id,'customer_account_id'=>$account->id,'sale_id'=>$sale->id,'created_at'=>now()]);
                app(CustomerOrderNotifications::class)->send($sale,'new_customer_order');
                return $sale;
            });
        });
    }
}
