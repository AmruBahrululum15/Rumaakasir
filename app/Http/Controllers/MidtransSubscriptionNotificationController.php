<?php
namespace App\Http\Controllers;
use App\Services\{SubscriptionService,DokuService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class MidtransSubscriptionNotificationController extends Controller {
 public function __invoke(Request $r,SubscriptionService $service){
  $d=$r->validate(['order_id'=>'required|string|max:50','status_code'=>'required|string','gross_amount'=>'required|string','signature_key'=>'required|string']);
  $key=(string)config('midtrans.server_key');abort_unless($key && hash_equals(hash('sha512',$d['order_id'].$d['status_code'].$d['gross_amount'].$key),$d['signature_key']),401);
  $host=config('midtrans.is_production')?'https://api.midtrans.com':'https://api.sandbox.midtrans.com';
  $response=Http::withBasicAuth($key,'')->acceptJson()->timeout(15)->withoutRedirecting()->get($host.'/v2/'.rawurlencode($d['order_id']).'/status');
  abort_unless($response->successful(),503);$data=$response->json();
  abort_unless(($data['order_id']??null)===$d['order_id']&&DokuService::amountMatches($data['gross_amount']??null,(int) $d['gross_amount']),422);
  $order=\App\Models\SubscriptionOrder::withoutGlobalScopes()->where('order_id',$d['order_id'])->where('payment_provider','midtrans')->firstOrFail();
  abort_unless(DokuService::amountMatches($data['gross_amount']??null,$order->amount),422);
  $status=$data['transaction_status']??'';$paid=$status==='settlement'||($status==='capture'&&($data['fraud_status']??null)==='accept');
  if($paid)$service->notify(['order'=>['invoice_number'=>$order->order_id,'amount'=>$order->amount],'transaction'=>['status'=>'SUCCESS'],'channel'=>['id'=>$data['payment_type']??'midtrans']], 'midtrans');
  else \Illuminate\Support\Facades\DB::transaction(function()use($order,$status){$row=\App\Models\SubscriptionOrder::withoutGlobalScopes()->lockForUpdate()->findOrFail($order->id);if($row->paid_at)return;$row->update(['status'=>match($status){'expire'=>'expired','cancel','deny','failure'=>'failed',default=>'pending'},'last_payment_status'=>$status]);});
  return response()->json(['message'=>'OK']);
 }
}
