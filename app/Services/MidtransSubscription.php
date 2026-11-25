<?php
namespace App\Services;
use App\Models\{SubscriptionOrder,User};
use App\Exceptions\DokuCheckoutException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
class MidtransSubscription {
 public function ensureConfigured():void {
  $key=(string)config('midtrans.server_key');
  if(!$key || str_starts_with($key,'SB-')===(bool)config('midtrans.is_production'))throw ValidationException::withMessages(['payment'=>'Server Key Midtrans tidak sesuai dengan environment pembayaran.']);
 }
 public function validCheckoutUrl(?string $url):bool {
  $p=parse_url($url??'');return $p && ($p['scheme']??'')==='https' && in_array($p['host']??'', ['app.midtrans.com','app.sandbox.midtrans.com'],true) && !isset($p['user'])&&!isset($p['pass'])&&!isset($p['port']);
 }
 public function createPayment(SubscriptionOrder $order,User $user):array {
  $this->ensureConfigured();$host=config('midtrans.is_production')?'https://app.midtrans.com':'https://app.sandbox.midtrans.com';
  try{$r=Http::withBasicAuth(config('midtrans.server_key'),'')->acceptJson()->timeout(30)->withoutRedirecting()->withHeaders(['X-Override-Notification'=>route('midtrans.subscription.notification')])->post($host.'/snap/v1/transactions',[
   'transaction_details'=>['order_id'=>$order->order_id,'gross_amount'=>$order->amount],
   'item_details'=>[['id'=>(string)$order->plan_id,'price'=>$order->amount,'quantity'=>1,'name'=>$order->plan_name.' '.$order->billing_cycle]],
   'customer_details'=>['first_name'=>$user->name,'email'=>$user->email],
   'callbacks'=>['finish'=>route('subscriptions.result',$order->order_id)],
   'expiry'=>['unit'=>'minutes','duration'=>60],
  ]);}catch(\Illuminate\Http\Client\ConnectionException $e){throw new DokuCheckoutException();}
  if(!$r->successful())throw new DokuCheckoutException(in_array($r->status(),[400,401,403,422],true));
  if(!$this->validCheckoutUrl($r->json('redirect_url')))throw new DokuCheckoutException();
  return ['payment_url'=>$r->json('redirect_url')];
 }
}
