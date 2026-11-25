<?php
namespace Tests\Feature;
use Tests\TestCase;
use Tests\Concerns\RefreshDatabase;
use App\Models\{User,SubscriptionOrder};
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;
class MidtransSubscriptionTest extends TestCase {
 use RefreshDatabase;
 public function test_hosted_checkout_and_verified_idempotent_activation():void {
  config(['doku.subscription_gateway'=>'midtrans','midtrans.is_production'=>false,'midtrans.server_key'=>'SB-test-secret']);Http::preventStrayRequests();
  Http::fake(['app.sandbox.midtrans.com/*'=>Http::response(['redirect_url'=>'https://app.sandbox.midtrans.com/snap/v3/redirection/test','token'=>'test'],201),'api.sandbox.midtrans.com/*'=>fn($r)=>Http::response(['order_id'=>basename(dirname($r->url())),'gross_amount'=>'49000.00','transaction_status'=>'settlement','payment_type'=>'bank_transfer'])]);
  $user=User::factory()->create();$user->assignRole(Role::findOrCreate('Atasan','web'));
  $this->actingAs($user)->post(route('subscriptions.store'),['billing_cycle'=>'monthly','amount'=>1])->assertRedirect('https://app.sandbox.midtrans.com/snap/v3/redirection/test');
  $order=SubscriptionOrder::withoutGlobalScopes()->latest('id')->firstOrFail();$this->assertSame('midtrans',$order->payment_provider);$this->assertSame('pending',$order->status);$this->assertNull($order->paid_at);
  $this->get(route('subscriptions.result',$order->order_id).'?transaction_status=settlement')->assertOk();$this->assertNull($order->fresh()->paid_at);
  $data=['order_id'=>$order->order_id,'status_code'=>'200','gross_amount'=>'49000.00','transaction_status'=>'settlement'];$data['signature_key']=hash('sha512',$data['order_id'].$data['status_code'].$data['gross_amount'].'SB-test-secret');
  $this->postJson(route('midtrans.subscription.notification'),array_replace($data,['signature_key'=>'fake']))->assertUnauthorized();
  $this->postJson(route('midtrans.subscription.notification'),$data)->assertOk();$this->assertSame('paid',$order->fresh()->status);$end=$user->business->fresh()->subscription_ends_at;
  $this->postJson(route('midtrans.subscription.notification'),$data)->assertOk();$this->assertEquals($end,$user->business->fresh()->subscription_ends_at);
 }
}
