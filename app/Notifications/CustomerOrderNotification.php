<?php
namespace App\Notifications;
use App\Models\Sale;
use Illuminate\Notifications\Notification;
class CustomerOrderNotification extends Notification {
    public function __construct(public Sale $sale, public string $event) {}
    public function via(object $notifiable):array {return ['database'];}
    public function toDatabase(object $notifiable):array {
        $customer=\App\Models\Customer::withoutGlobalScopes()->where('business_id',$this->sale->business_id)->find($this->sale->customer_id);
        $name=$customer?->name??'Pelanggan';
        return ['type'=>$this->event,'title'=>$this->event==='payment_received'?'Pembayaran diterima':'Pesanan baru dari '.$name,
            'message'=>$this->sale->code.' • '.strtoupper($this->sale->payment_method).' • Rp'.number_format($this->sale->total,0,',','.'),
            'order_id'=>$this->sale->id,'order_number'=>$this->sale->code,'sale_id'=>$this->sale->id,'sale_code'=>$this->sale->code,
            'customer_name'=>$name,'business_id'=>$this->sale->business_id,'total'=>(float)$this->sale->total,'order_type'=>$this->sale->order_source,
            'payment_method'=>$this->sale->payment_method,'created_at'=>now()->toIso8601String(),
            'url'=>route($this->sale->order_source==='customer_portal'?'portal-orders.show':'sales.show',$this->sale->id)];
    }
}
