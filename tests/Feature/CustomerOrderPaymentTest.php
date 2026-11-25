<?php
namespace Tests\Feature;
use App\Models\{Business,BusinessPaymentSetting,Category,CustomerAccount,CustomerOrderPayment,Product,Sale,User};
use App\Services\{CustomerPortal,PortalOrderService};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{DB,Http,Mail,Notification,Storage};
use Illuminate\Support\Str;
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderPaymentTest extends TestCase {
    use RefreshDatabase;
    protected function setUp():void {parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();Storage::fake('local');}
    private function store():array {
        $b=Business::create(['name'=>'Toko '.uniqid(),'subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>'retail'])->save();
        $product=app(CustomerPortal::class)->within($b,function(){$c=Category::create(['name'=>'Makanan','slug'=>'makanan']);return Product::create(['name'=>'Menu Toko','category_id'=>$c->id,'price'=>25000,'stock'=>50,'is_active'=>true]);});
        $a=CustomerAccount::create(['name'=>'Amru','email'=>uniqid().'@example.test','phone'=>'08123456789','password'=>'password']);
        return [$b,$product,$a];
    }
    private function staff(Business $business,string $role='Kasir',bool $allowed=true):User {
        app(TenantContext::class)->set($business);$u=User::factory()->create();app(TenantContext::class)->clear();
        $u->assignRole(Role::findOrCreate($role,'web'));
        if($allowed)foreach(['sales.view','sales.create','settings.edit'] as $name)$u->givePermissionTo(Permission::findOrCreate($name,'web'));
        return $u;
    }
    private function order(Business $b,Product $p,CustomerAccount $a,string $method='cash'):Sale {return app(PortalOrderService::class)->create($b,$a,[$p->id=>2],(string)Str::uuid(),null,$method);}
    private function gateway(Business $b):void {BusinessPaymentSetting::create(['business_id'=>$b->id,'cash_enabled'=>true,'qris_enabled'=>true,'qris_mode'=>'midtrans','va_enabled'=>true,'va_banks'=>['bca_va','bni_va'],'midtrans_server_key'=>'tenant-secret-'.$b->id]);}
    private function callbackPayload(Sale $sale,string $status='settlement',string $type='bank_transfer',?string $amount=null):array {
        $amount??=number_format($sale->total,2,'.','');
        $payload=['order_id'=>$sale->code,'gross_amount'=>$amount,'status_code'=>'200','transaction_status'=>$status,'payment_type'=>$type,'fraud_status'=>'accept'];
        $payload['signature_key']=hash('sha512',$sale->code.'200'.$amount.'tenant-secret-'.$sale->business_id);
        return $payload;
    }
    public function test_order_notifies_only_allowed_roles_in_its_store_and_is_idempotent():void {
        [$b,$p,$a]=$this->store();[$other]=$this->store();
        $recipients=[];foreach(['Atasan','Admin','Kasir'] as $role)$recipients[]=$this->staff($b,$role);
        $foreign=$this->staff($other);$denied=$this->staff($b,'Kasir',false);$inactive=$this->staff($b);$inactive->update(['is_active'=>false]);
        $token=(string)Str::uuid();$service=app(PortalOrderService::class);$sale=$service->create($b,$a,[$p->id=>2],$token,null);
        $service->create($b,$a,[$p->id=>2],$token,null);
        $this->assertDatabaseCount('sales',1);$this->assertDatabaseCount('notifications',3);
        foreach($recipients as $user){$data=$user->notifications()->firstOrFail()->data;$this->assertEquals($b->id,$data['business_id']);$this->assertEquals($sale->id,$data['order_id']);$this->assertEquals('Amru',$data['customer_name']);$this->assertEquals(50000,$data['total']);}
        foreach([$foreign,$denied,$inactive] as $user)$this->assertEquals(0,$user->notifications()->count());
    }
    public function test_navbar_unread_click_and_mark_all_are_private():void {
        [$b,$p,$a]=$this->store();[$other]=$this->store();$staff=$this->staff($b);$foreign=$this->staff($other);$sale=$this->order($b,$p,$a);
        $note=$staff->notifications()->firstOrFail();
        $this->actingAs($staff)->getJson(route('notifications.feed'))->assertOk()->assertJsonPath('unread_count',1)->assertSee('Pesanan baru dari Amru');
        $this->get(route('portal-orders.show',$sale->id))->assertOk()->assertSee('order-notification-navbar')->assertSee('50.000');
        $this->post(route('notifications.open',$note->id))->assertRedirect(route('portal-orders.show',$sale->id));
        $this->getJson(route('notifications.feed'))->assertJsonPath('unread_count',0);
        $this->actingAs($foreign)->post(route('notifications.open',$note->id))->assertNotFound();
        $this->get(route('portal-orders.show',$sale->id))->assertNotFound();
        $staff->notifications()->create(['id'=>(string)Str::uuid(),'type'=>'test','data'=>['business_id'=>$other->id,'title'=>'Foreign secret']]);
        $this->actingAs($staff)->getJson(route('notifications.feed'))->assertJsonPath('unread_count',0)->assertDontSee('Foreign secret');
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertRedirect();
        $this->getJson(route('notifications.feed'))->assertJsonPath('unread_count',1);
        $this->patch(route('notifications.read-all'))->assertRedirect();
        $this->getJson(route('notifications.feed'))->assertJsonPath('unread_count',0);
        $this->assertEquals(1,$staff->unreadNotifications()->count());
    }
    public function test_cash_amount_is_server_owned_and_workflow_requires_payment_to_complete():void {
        [$b,$p,$a]=$this->store();$staff=$this->staff($b);
        $this->actingAs($a,'customer')->post(route('customer.cart.add',$b->slug),['product_id'=>$p->id,'quantity'=>2]);
        $token=session('portal_cart.'.$a->id.'.'.$b->id.'.token');
        $this->post(route('customer.cart.checkout',$b->slug),['token'=>$token,'payment_method'=>'cash','total'=>1,'payment_status'=>'PAID','business_id'=>1])->assertRedirect();
        $sale=Sale::withoutGlobalScopes()->where('business_id',$b->id)->firstOrFail();
        $this->assertEquals(50000,$sale->total);$this->assertEquals('CASH_PENDING',$sale->payment_status);$this->assertEquals('pending',$sale->order_status);
        $this->get(route('customer.orders.show',$sale->id).'?payment_status=PAID')->assertOk()->assertSee('Pembayaran dilakukan secara tunai');$this->assertEquals('CASH_PENDING',$sale->fresh()->payment_status);
        $this->actingAs($staff)->patch(route('portal-orders.update',$sale->id),['action'=>'confirmed'])->assertRedirect();
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'processing'])->assertRedirect();
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'completed'])->assertStatus(422);
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertRedirect();
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertStatus(422);
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'completed'])->assertRedirect();
        $this->assertEquals('PAID',$sale->fresh()->payment_status);$this->assertEquals('completed',$sale->fresh()->order_status);
        $this->assertEquals(1,$staff->notifications()->where('data->type','payment_received')->count());
    }
    public function test_manual_qris_uses_order_store_snapshot_and_is_not_public():void {
        [$b,$p,$a]=$this->store();[$other,$otherProduct,$otherAccount]=$this->store();
        Storage::disk('local')->put('business-qris/'.$b->id.'/qr.png','QR-A');Storage::disk('local')->put('business-qris/'.$other->id.'/qr.png','QR-B');
        foreach([$b,$other] as $store)BusinessPaymentSetting::create(['business_id'=>$store->id,'qris_enabled'=>true,'qris_mode'=>'manual','qris_path'=>'business-qris/'.$store->id.'/qr.png']);
        $sale=$this->order($b,$p,$a,'qris');$foreignSale=$this->order($other,$otherProduct,$otherAccount,'qris');
        $this->actingAs($a,'customer')->get(route('customer.orders.show',$sale->id))->assertOk()->assertSee($b->name)->assertDontSee($other->name);
        $response=$this->get(route('customer.orders.qris',$sale->id));$response->assertOk()->assertHeader('Cache-Control','no-store, private');$this->assertEquals('QR-A',file_get_contents($response->baseResponse->getFile()->getPathname()));
        $this->get(route('customer.orders.qris',$foreignSale->id))->assertNotFound();$this->get(route('customer.orders.show',$foreignSale->id))->assertNotFound();
        $staff=$this->staff($b);$this->actingAs($staff)->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertRedirect();$this->assertEquals('PAID',$sale->fresh()->payment_status);
    }
    public function test_methods_are_store_specific_and_va_hidden_without_merchant_configuration():void {
        [$b,$p,$a]=$this->store();[$other]=$this->store();$this->gateway($other);
        $this->actingAs($a,'customer')->post(route('customer.cart.add',$b->slug),['product_id'=>$p->id,'quantity'=>1]);
        $this->get(route('customer.cart',$b->slug))->assertOk()->assertViewHas('methods',fn($m)=>array_keys($m)===['cash'])->assertDontSee('Virtual Account');
        $token=session('portal_cart.'.$a->id.'.'.$b->id.'.token');
        $this->post(route('customer.cart.checkout',$b->slug),['token'=>$token,'payment_method'=>'va','business_id'=>$other->id])->assertSessionHasErrors('payment_method');$this->assertDatabaseCount('sales',0);
        $this->gateway($b);$this->get(route('customer.cart',$b->slug))->assertViewHas('methods',fn($m)=>array_keys($m)===['cash','qris','va']);
    }
    public function test_va_creation_uses_tenant_key_real_gateway_url_and_never_pays_on_return():void {
        [$b,$p,$a]=$this->store();$this->gateway($b);$sale=$this->order($b,$p,$a,'va');
        Http::fake(['app.sandbox.midtrans.com/*'=>Http::response(['token'=>'provider-token','redirect_url'=>'https://app.sandbox.midtrans.com/snap/v2/provider-token'])]);
        $this->actingAs($a,'customer')->post(route('customer.orders.pay',$sale->id))->assertRedirect();
        Http::assertSent(fn($r)=>$r->hasHeader('Authorization','Basic '.base64_encode('tenant-secret-'.$b->id.':'))&&$r['transaction_details']['gross_amount']===50000&&$r['enabled_payments']===['bca_va','bni_va']);
        $this->post(route('customer.orders.pay',$sale->id))->assertRedirect();Http::assertSentCount(1);
        $this->get(route('customer.orders.show',$sale->id).'?transaction_status=settlement')->assertOk()->assertSee('Pilih Bank')->assertSee('provider-token');$this->assertEquals('PENDING',$sale->fresh()->payment_status);
        $staff=$this->staff($b);$this->actingAs($staff)->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertStatus(422);
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'cancelled'])->assertStatus(422);
        $this->assertStringNotContainsString('tenant-secret',DB::table('business_payment_settings')->where('business_id',$b->id)->value('midtrans_server_key'));
    }
    public function test_verified_qris_and_va_callbacks_are_idempotent_and_reject_foreign_or_forged_callbacks():void {
        foreach([['qris','qris'],['qris','shopeepay'],['va','bank_transfer']] as [$method,$providerType]){
            [$b,$p,$a]=$this->store();$this->gateway($b);$staff=$this->staff($b);$sale=$this->order($b,$p,$a,$method);
            $payload=$this->callbackPayload($sale,'settlement',$providerType);
            Http::fake(['api.sandbox.midtrans.com/v2/'.$sale->code.'/status'=>Http::response($payload)]);
            $url=route('customer-payment.webhook',$b->id);
            $this->postJson($url,array_replace($payload,['signature_key'=>str_repeat('a',128)]))->assertForbidden();
            $this->postJson(route('customer-payment.webhook',1),$payload)->assertNotFound();
            $this->postJson($url,$payload)->assertOk();$this->postJson($url,$payload)->assertOk();
            $this->assertEquals('PAID',$sale->fresh()->payment_status);$this->assertEquals(50000,$sale->fresh()->paid);$this->assertEquals(48,$p->fresh()->stock);
            $this->assertEquals(1,$staff->notifications()->where('data->type','payment_received')->count());
        }
    }
    public function test_unverified_amount_pending_status_and_duplicate_expiry_do_not_pay_or_restore_twice():void {
        [$b,$p,$a]=$this->store();$this->gateway($b);$sale=$this->order($b,$p,$a,'va');$payload=$this->callbackPayload($sale);
        Http::fake(['api.sandbox.midtrans.com/*'=>Http::sequence()
            ->push(array_replace($payload,['gross_amount'=>'1.00']))
            ->push(array_replace($payload,['transaction_status'=>'pending','va_numbers'=>[['bank'=>'bca','va_number'=>'PROVIDER-VA']],'expiry_time'=>'2026-10-07 12:00:00']))
            ->push(array_replace($payload,['transaction_status'=>'expire']))
            ->push(array_replace($payload,['transaction_status'=>'expire']))]);
        $this->postJson(route('customer-payment.webhook',$b->id),$payload)->assertUnprocessable();
        $this->postJson(route('customer-payment.webhook',$b->id),$payload)->assertOk();$this->assertEquals('PENDING',$sale->fresh()->payment_status);
        $this->actingAs($a,'customer')->get(route('customer.orders.show',$sale->id))->assertSee('PROVIDER-VA');
        $this->postJson(route('customer-payment.webhook',$b->id),$payload)->assertOk();$this->postJson(route('customer-payment.webhook',$b->id),$payload)->assertOk();
        $this->assertEquals('EXPIRED',$sale->fresh()->payment_status);$this->assertEquals(50,$p->fresh()->stock);
    }
    public function test_gateway_error_preserves_unpaid_order_and_does_not_retry_uncertain_request():void {
        [$b,$p,$a]=$this->store();$this->gateway($b);$sale=$this->order($b,$p,$a,'va');
        Http::fake(['app.sandbox.midtrans.com/*'=>Http::response(['error_messages'=>['unavailable']],500)]);
        $this->actingAs($a,'customer')->post(route('customer.orders.pay',$sale->id))->assertSessionHasErrors('payment');
        $this->post(route('customer.orders.pay',$sale->id))->assertRedirect();Http::assertSentCount(1);
        $this->assertEquals('PENDING',$sale->fresh()->payment_status);$this->assertEquals('uncertain',CustomerOrderPayment::where('sale_id',$sale->id)->first()->gateway_state);
    }
    public function test_payment_settings_require_owner_permission_and_preserve_other_store():void {
        [$b]=$this->store();[$other]=$this->store();$owner=$this->staff($b,'Atasan');$cashier=$this->staff($b);
        $this->actingAs($cashier)->get(route('settings.customer-payments'))->assertForbidden();
        $this->actingAs($owner)->get(route('settings.customer-payments'))->assertOk();
        $this->put(route('settings.customer-payments.update'),['business_id'=>$other->id,'cash_enabled'=>true,'qris_enabled'=>false,'va_enabled'=>true,'qris_mode'=>'midtrans','midtrans_production'=>false,'midtrans_server_key'=>'secret','va_banks'=>['bca_va']])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('business_payment_settings',['business_id'=>$b->id,'va_enabled'=>true]);$this->assertDatabaseMissing('business_payment_settings',['business_id'=>$other->id]);
        $this->get(route('settings.customer-payments'))->assertDontSee('value="secret"',false);
    }
}
