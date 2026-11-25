<?php
namespace App\Services;
use App\Models\{Business,Customer,Promotion,PromotionUsage,Sale};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class PromotionService {
    /** Caller holds its checkout transaction until record() completes. Only one best promotion applies. */
    public function quote(Business $business,Customer $customer,float $total,?string $code): array {
        abort_unless((int)$customer->business_id===(int)$business->id,404);
        $code=strtoupper(trim($code ?? ''));
        if(!app(PackageAccess::class)->allows('promotions',$business)) {
            if($code!=='') throw ValidationException::withMessages(['voucher_code'=>'Promo tidak tersedia.']);
            return [null,0];
        }
        $query=Promotion::where('business_id',$business->id)->where('is_active',true)->where('start_at','<=',now())->where('end_at','>=',now());
        $code!=='' ? $query->where('code',$code) : $query->whereNull('code');
        $best=null;$amount=0;
        foreach($query->orderBy('id')->lockForUpdate()->get() as $promo) {
            if($total<(float)$promo->min_transaction) continue;
            $used=DB::table('promotion_usages')->where('promotion_usages.business_id',$business->id)->where('promotion_id',$promo->id)
                ->join('sales',fn($j)=>$j->on('sales.id','=','promotion_usages.sale_id')->where('sales.business_id',$business->id))
                ->whereNotIn('sales.payment_status',['FAILED','CANCELLED','EXPIRED','VOIDED','REFUNDED']);
            if($promo->usage_limit!==null && (clone $used)->count()>=$promo->usage_limit) continue;
            if($promo->per_customer_limit!==null && (clone $used)->where('promotion_usages.customer_id',$customer->id)->count()>=$promo->per_customer_limit) continue;
            $discount=$promo->discount_type==='percentage' ? round($total*(float)$promo->discount_value/100,2) : (float)$promo->discount_value;
            if($promo->max_discount!==null) $discount=min($discount,(float)$promo->max_discount);
            $discount=min($total,$discount);
            if($discount>$amount) {$best=$promo;$amount=$discount;}
        }
        if($code!=='' && !$best) throw ValidationException::withMessages(['voucher_code'=>'Voucher tidak berlaku, kedaluwarsa, atau batas pemakaian tercapai.']);
        return [$best,$amount];
    }
    public function record(?Promotion $promotion,Sale $sale,float $discount): void {
        if(!$promotion) return;
        abort_unless((int)$promotion->business_id===(int)$sale->business_id,404);
        PromotionUsage::create(['promotion_id'=>$promotion->id,'sale_id'=>$sale->id,'customer_id'=>$sale->customer_id,'discount'=>$discount,'created_at'=>now()]);
    }
}
