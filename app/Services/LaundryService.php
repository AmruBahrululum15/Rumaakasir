<?php
namespace App\Services;
use App\Models\{BusinessService,Customer,LaundryOrder,LaundryPayment,LaundryStatusHistory,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class LaundryService {
    public const STATUSES=['received'=>'Diterima','washing'=>'Dicuci','drying'=>'Dikeringkan','ironing'=>'Disetrika','ready'=>'Selesai','picked_up'=>'Diambil'];
    private function authorize(User $user): void {app(PackageAccess::class)->ensureFeature('laundry_services',$user->business);abort_unless($user->business->business_type==='laundry',403);}
    public function create(User $user,array $data): LaundryOrder {
        $this->authorize($user);
        return DB::transaction(function()use($user,$data){
            $service=BusinessService::where('business_id',$user->business_id)->where('kind','laundry')->where('is_active',true)->lockForUpdate()->findOrFail($data['service_id']);
            Customer::where('business_id',$user->business_id)->findOrFail($data['customer_id']);
            if($service->unit==='unit' && floor($data['quantity'])!==(float)$data['quantity'])throw ValidationException::withMessages(['quantity'=>'Layanan satuan membutuhkan jumlah bulat.']);
            $order=LaundryOrder::create(['customer_id'=>$data['customer_id'],'service_id'=>$service->id,'service_name'=>$service->name,'unit'=>$service->unit,'quantity'=>$data['quantity'],'price'=>$service->price,'total'=>round($data['quantity']*(float)$service->price,2),'estimated_at'=>$data['estimated_at'],'notes'=>$data['notes'] ?? null]);
            $this->history($user,$order,'received');return $order;
        });
    }
    public function transition(User $user,int $id,string $status): void {
        $this->authorize($user);
        DB::transaction(function()use($user,$id,$status){
            $order=LaundryOrder::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($id);
            $keys=array_keys(self::STATUSES);$next=$keys[array_search($order->status,$keys,true)+1] ?? null;
            if($next!==$status)throw ValidationException::withMessages(['status'=>'Status harus mengikuti urutan proses laundry.']);
            if($status==='picked_up' && (float)$order->paid<(float)$order->total)throw ValidationException::withMessages(['status'=>'Pembayaran harus lunas sebelum diambil.']);
            $order->update(['status'=>$status]);$this->history($user,$order,$status);
        });
    }
    public function pay(User $user,int $id,string $requestKey,float $amount): void {
        $this->authorize($user);
        DB::transaction(function()use($user,$id,$requestKey,$amount){
            $order=LaundryOrder::where('business_id',$user->business_id)->lockForUpdate()->findOrFail($id);
            $existing=LaundryPayment::where('request_key',$requestKey)->first();
            if($existing){abort_unless((int)$existing->laundry_order_id===$id && (float)$existing->amount===$amount,409);return;}
            if($amount<=0 || round((float)$order->paid+$amount,2)>(float)$order->total)throw ValidationException::withMessages(['amount'=>'Nominal pembayaran melebihi sisa tagihan atau tidak valid.']);
            LaundryPayment::create(['laundry_order_id'=>$id,'user_id'=>$user->id,'request_key'=>$requestKey,'amount'=>$amount,'created_at'=>now()]);$order->increment('paid',$amount);
        });
    }
    private function history(User $user,LaundryOrder $order,string $status):void {LaundryStatusHistory::create(['laundry_order_id'=>$order->id,'user_id'=>$user->id,'status'=>$status,'created_at'=>now()]);}
}
