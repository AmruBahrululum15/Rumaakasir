<?php
namespace App\Services;
use App\Models\{Business,BusinessPaymentSetting,CustomerOrderPayment,Sale};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class CustomerPaymentMethods {
    public const BANKS=['bca_va'=>'BCA','bni_va'=>'BNI','bri_va'=>'BRI','echannel'=>'Mandiri','permata_va'=>'Permata'];
    public function settings(Business $business): BusinessPaymentSetting {
        return BusinessPaymentSetting::firstOrNew(['business_id'=>$business->id],['cash_enabled'=>true,'qris_enabled'=>false,'va_enabled'=>false,'qris_mode'=>'manual','va_banks'=>[]]);
    }
    public function available(Business $business): array {
        $s=$this->settings($business);$methods=[];
        if($s->cash_enabled)$methods['cash']='Cash';
        if($s->qris_enabled && ($s->qris_mode==='midtrans' ? filled($s->midtrans_server_key) : ($s->qris_path && Storage::disk('local')->exists($s->qris_path))))$methods['qris']='QRIS';
        if($s->va_enabled && filled($s->midtrans_server_key) && array_intersect(array_keys(self::BANKS),$s->va_banks??[]))$methods['va']='Virtual Account';
        return $methods;
    }
    public function snapshot(Sale $sale, Business $business): void {
        if(!array_key_exists($sale->payment_method,$this->available($business)))throw ValidationException::withMessages(['payment_method'=>'Metode pembayaran tidak tersedia di toko ini.']);
        $s=$this->settings($business);
        $provider=$sale->payment_method==='cash'?'cash':($sale->payment_method==='qris'&&$s->qris_mode==='manual'?'manual_qris':'midtrans');
        if($provider==='midtrans' && ((float)$sale->total<=0 || (float)$sale->total!==floor((float)$sale->total)))throw ValidationException::withMessages(['payment_method'=>'Pembayaran gateway membutuhkan total rupiah bulat lebih dari nol. Pilih metode lain atau hubungi toko.']);
        CustomerOrderPayment::create(['business_id'=>$business->id,'sale_id'=>$sale->id,'provider'=>$provider,
            'qris_path'=>$provider==='manual_qris'?$s->qris_path:null,'server_key'=>$provider==='midtrans'?$s->midtrans_server_key:null,
            'production'=>$s->midtrans_production??false,'enabled_payments'=>$sale->payment_method==='va'?array_values(array_intersect(array_keys(self::BANKS),$s->va_banks??[])):['other_qris']]);
    }
}
