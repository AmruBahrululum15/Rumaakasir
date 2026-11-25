<?php

namespace Tests\Feature;

use App\Models\{Package, SubscriptionOrder, User};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class DokuRetryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['doku.subscription_gateway'=>'doku','doku.env'=>'production','doku.base_url'=>'https://api.doku.com','doku.client_id'=>'production-test-client','doku.secret_key'=>'production-test-secret']);
        Http::preventStrayRequests();
        $owner=User::factory()->create();$owner->assignRole(Role::findOrCreate('Atasan','web'));$this->actingAs($owner);
    }

    private function failed(): SubscriptionOrder
    {
        return SubscriptionOrder::create(['order_id'=>'SUB-'.Str::ulid(),'request_id'=>(string)Str::uuid(),
            'plan_id'=>Package::where('code','premium')->firstOrFail()->id,'plan_name'=>'Premium','business_name'=>'Store',
            'billing_cycle'=>'monthly','amount'=>49000,'status'=>'failed','last_payment_status'=>'CREATE_ERROR','payment_due_at'=>now()->addHour()]);
    }

    public function test_retry_uses_server_price_new_ids_and_double_click_reuses_payment(): void
    {
        $source=$this->failed();Package::where('code','premium')->update(['price_monthly'=>75000]);
        Http::fake(function($request) use($source) {
            $this->assertSame('https://api.doku.com/checkout/v1/payment',$request->url());
            $this->assertSame(75000,$request['order']['amount']);
            $this->assertNotSame($source->order_id,$request['order']['invoice_number']);
            $this->assertNotSame($source->request_id,$request->header('Request-Id')[0]);
            return Http::response(['response'=>['order'=>['invoice_number'=>$request['order']['invoice_number'],'amount'=>75000],
                'payment'=>['url'=>'https://jokul.doku.com/checkout-link-v2/retry','expired_date'=>now()->addHour()->setTimezone('Asia/Jakarta')->format('YmdHis')]]]);
        });
        for($i=0;$i<2;$i++)$this->post(route('subscriptions.retry',$source->order_id),['amount'=>1,'business_id'=>999,'billing_cycle'=>'yearly'])
            ->assertRedirect('https://jokul.doku.com/checkout-link-v2/retry');
        Http::assertSentCount(1);$this->assertDatabaseCount('subscription_orders',2);
        $new=SubscriptionOrder::latest('id')->firstOrFail();$this->assertSame('pending',$new->status);$this->assertNull($new->paid_at);
        $this->assertSame('free',auth()->user()->business->fresh()->subscription_plan);
    }

    public function test_failed_retry_is_also_idempotent_and_records_safe_diagnostics(): void
    {
        $source=$this->failed();Log::spy();
        Http::fake(['*'=>Http::response(['error'=>['code'=>'invalid_signature','message'=>'Invalid Header Signature'],'secret_key'=>'production-test-secret'],401)]);
        $this->post(route('subscriptions.retry',$source->order_id))->assertRedirect();
        $this->post(route('subscriptions.retry',$source->order_id))->assertRedirect();
        Http::assertSentCount(1);$this->assertDatabaseCount('subscription_orders',2);
        Log::shouldHaveReceived('warning')->once()->withArgs(function($message,$context){
            return $context['http_status']===401 && $context['request_target']==='/checkout/v1/payment'
                && isset($context['request_timestamp'],$context['body_digest'],$context['canonical'])
                && str_starts_with($context['canonical'],'Client-Id:[MASKED]')
                && !str_contains(json_encode($context),'production-test-secret');
        });
    }

    public function test_uncertain_paid_and_foreign_orders_cannot_create_retry_payments(): void
    {
        Http::fake();$order=$this->failed();
        foreach(['creating','create_unknown','paid'] as $status){
            $order->update(['status'=>$status]);
            $this->post(route('subscriptions.retry',$order->order_id))->assertRedirect(route('subscriptions.result',$order->order_id));
        }
        $order->update(['status'=>'failed']);
        $foreign = \App\Models\Business::create(['name'=>'Other Store']);
        \Illuminate\Support\Facades\DB::table('subscription_orders')->where('id',$order->id)->update(['business_id'=>$foreign->id]);
        $this->post(route('subscriptions.retry',$order->order_id))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_status_page_shows_badges_and_safe_actions_without_technical_errors(): void
    {
        $order=$this->failed();
        foreach(['failed'=>'GAGAL','pending'=>'MENUNGGU PEMBAYARAN','expired'=>'KADALUARSA','paid'=>'BERHASIL'] as $status=>$label){
            $order->update(['status'=>$status]);
            $response=$this->get(route('subscriptions.result',$order->order_id))->assertOk()->assertSee($label)
                ->assertSee('subscription-payment.css')->assertSee('Rp 49.000')->assertSee('Periksa Status')->assertDontSee('log aplikasi')->assertDontSee('CREATE_ERROR');
            if($status==='failed')$response->assertSee('Pembayaran belum dapat diproses.')->assertSee('Coba Bayar Lagi')->assertSee(route('subscriptions.retry',$order->order_id));
        }
    }
}
