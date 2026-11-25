<?php
namespace App\Services;
use App\Models\{Business,Customer,CustomerLoyaltyPoint,Sale};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class LoyaltyService {
    public function settings(Business $business): array {
        $value=DB::table('settings')->where('business_id',$business->id)->where('key','loyalty_configuration')->value('value');
        return array_replace(['loyalty_enabled'=>false,'spend_per_point'=>10000,'point_value'=>100,'minimum_redeem_points'=>10],json_decode($value ?? '{}',true) ?: []);
    }
    public function enabled(Business $business): bool {
        return $business->business_type!==null && app(PackageAccess::class)->allows('customer_loyalty',$business) && $this->settings($business)['loyalty_enabled'];
    }
    public function balance(int $businessId,int $customerId): int {
        return (int) DB::table('customer_loyalty_points')->where('business_id',$businessId)->where('customer_id',$customerId)->sum('points');
    }
    public function quote(Business $business,Customer $customer,int $points,float $total): float {
        abort_unless((int)$customer->business_id===(int)$business->id,404);
        if($points===0) return 0;
        if(!$this->enabled($business)) throw ValidationException::withMessages(['redeem_points'=>'Loyalty tidak aktif.']);
        $settings=$this->settings($business);
        if($points<0 || $points<$settings['minimum_redeem_points'] || $points>$this->balance($business->id,$customer->id)) throw ValidationException::withMessages(['redeem_points'=>'Poin tidak mencukupi atau di bawah minimum redeem.']);
        $discount=round($points*$settings['point_value'],2);
        if($discount>$total) throw ValidationException::withMessages(['redeem_points'=>'Nilai redeem melebihi total transaksi.']);
        return $discount;
    }
    /** Called only on a new paid sale or a transition to PAID, inside the sale transaction. */
    public function settle(Sale $sale): void {
        $business=Business::find($sale->business_id);
        if(!$business || !$this->enabled($business) || !$sale->customer_id || strtoupper($sale->payment_status)!=='PAID') return;
        DB::transaction(function() use($sale,$business) {
            $customer=Customer::withoutGlobalScopes()->where('business_id',$business->id)->lockForUpdate()->findOrFail($sale->customer_id);
            if(DB::table('customer_loyalty_points')->where('business_id',$business->id)->where('source_type','earned')->where('source_id',$sale->id)->exists()) return;
            $points=(int)$sale->loyalty_redeem_points;
            if($points>0) {
                if($points>$this->balance($business->id,$customer->id)) throw ValidationException::withMessages(['redeem_points'=>'Saldo poin telah berubah. Silakan ulangi pembayaran.']);
                $this->record($business->id,$customer->id,-$points,'redeemed',$sale->id,'Redeem '.$sale->code);
            }
            $earned=(int)floor((float)$sale->total/$this->settings($business)['spend_per_point']);
            $this->record($business->id,$customer->id,$earned,'earned',$sale->id,'Poin transaksi '.$sale->code);
        });
    }
    private function record(int $business,int $customer,int $points,string $type,int $source,string $description): void {
        DB::table('customer_loyalty_points')->insert(['business_id'=>$business,'customer_id'=>$customer,'points'=>$points,'source_type'=>$type,'source_id'=>$source,'description'=>$description,'created_at'=>now()]);
    }
}
