<?php

namespace Tests\Feature;

use App\Models\{Business, Package, PackageVariant, SubscriptionOrder, Table, User};
use App\Services\{BusinessModules, PackageAccess};
use App\Tenancy\TenantContext;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\{Http, Mail, Notification};
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\{Role, Permission};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class BusinessPackageVariantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); Notification::fake(); Http::preventStrayRequests();
        config(['doku.subscription_gateway'=>'doku', 'doku.client_id'=>'test-client', 'doku.secret_key'=>'test-secret', 'doku.env'=>'sandbox', 'doku.base_url'=>'https://api-sandbox.doku.com']);
        Http::fake(['api-sandbox.doku.com/*' => fn (ClientRequest $request) => Http::response(['response'=>[
            'order'=>['invoice_number'=>$request['order']['invoice_number'], 'amount'=>(string)$request['order']['amount'], 'session_id'=>'test-session'],
            'payment'=>['url'=>'https://sandbox.doku.com/checkout-link-v2/test', 'expired_date'=>now()->addHour()->setTimezone('Asia/Jakarta')->format('YmdHis')],
        ]])]);
    }

    private function owner(?string $type, string $code = 'free'): User
    {
        $business = Business::create(['name'=>'Toko '.($type ?? 'Legacy'), 'subscription_plan'=>$code, 'subscription_ends_at'=>$code === 'premium' ? now()->addMonth() : null]);
        $business->forceFill(['business_type'=>$type])->save();
        app(TenantContext::class)->set($business);
        try {
            $user = User::factory()->create(['no_telepon'=>'08123456789']);
            $user->assignRole(Role::findOrCreate('Atasan','web'));
            foreach (['tables.view','reports.view'] as $permission) $user->givePermissionTo(Permission::findOrCreate($permission,'web'));
            return $user;
        } finally { app(TenantContext::class)->clear(); }
    }

    private function variant(string $type, array $settings, string $code = 'premium'): void
    {
        PackageVariant::updateOrCreate(['package_id'=>Package::where('code',$code)->firstOrFail()->id, 'business_type'=>$type], ['settings'=>$settings]);
    }

    public static function types(): array
    {
        return array_map(fn ($type)=>[$type], ['retail','minimarket','cafe','restaurant','kedai','fashion','laundry','bengkel','salon','barbershop','jasa','lainnya']);
    }

    #[DataProvider('types')]
    public function test_available_matrix_matches_type_without_advertising_unbuilt_modules(string $type): void
    {
        $owner = $this->owner($type, 'premium'); $business = $owner->business;
        $modules = app(BusinessModules::class); $access = app(PackageAccess::class);
        $food = in_array($type,['cafe','restaurant','kedai']);
        $this->assertSame($food, $access->allows('qr_order',$business));
        $this->assertTrue($access->allows('export_excel',$business));
        $this->assertTrue($access->allows('custom_branding',$business));
        $keys = array_column($modules->comparison($type),'key');
        $this->assertSame($food, in_array('qr_order',$keys));
        $this->assertSame($food, in_array('max_tables',$keys));
        foreach (['laundry_delivery','service_orders','product_variants'] as $planned) {
            $this->assertFalse($modules->allows($business,$planned));
            $this->assertNotContains($planned,$keys);
        }
        $page = $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertSee('subscription-comparison')->assertSee('Perpanjang Premium');
        if (!$food) $page->assertDontSee('QR Order')->assertDontSee('Jumlah meja');
        $this->assertNotContains('stock_opname', $keys);
        $this->assertSame($type==='laundry', in_array('laundry_status',$keys));
        $this->assertContains('booking',$keys);
        $page->assertDontSee('Segera Hadir');
    }

    public function test_free_cafe_qr_redirects_owner_but_public_and_cross_tenant_access_are_denied(): void
    {
        $owner = $this->owner('cafe');
        app(TenantContext::class)->set($owner->business);
        $table = Table::create(['name'=>'Meja','qr_token'=>Str::random(30),'is_active'=>true]);
        app(TenantContext::class)->clear();
        $this->assertFalse(app(PackageAccess::class)->allows('qr_order',$owner->business));
        $this->actingAs($owner)->get(route('tables.qr',$table))->assertRedirect(route('subscriptions.index'))->assertSessionHas('info','QR Order tersedia pada paket Premium.');
        $this->getJson(route('tables.qr',$table))->assertForbidden();
        $this->get(route('customer.menu',$table->qr_token))->assertForbidden();
        $other = $this->owner('cafe');
        $this->actingAs($other)->get(route('tables.qr',$table))->assertNotFound();
        $this->get(route('dashboard'))->assertOk()->assertSee('QR Order')->assertSee('Premium');
    }

    public function test_variant_prices_are_used_for_monthly_yearly_checkout_without_client_overrides(): void
    {
        $this->variant('cafe',['price_monthly'=>79000,'price_yearly'=>790000]);
        $this->variant('retail',['price_monthly'=>59000,'price_yearly'=>590000]);
        foreach (['monthly'=>79000,'yearly'=>790000] as $cycle=>$amount) {
            $owner=$this->owner('cafe');
            $this->actingAs($owner)->post(route('subscriptions.store'), ['billing_cycle'=>$cycle, 'business_type'=>'retail', 'business_id'=>1, 'amount'=>1])
                ->assertRedirect('https://sandbox.doku.com/checkout-link-v2/test');
            $order=SubscriptionOrder::withoutGlobalScope('business')->where('business_id',$owner->business_id)->firstOrFail();
            $this->assertSame($amount,$order->amount);
            $this->assertSame('free',$owner->business->fresh()->subscription_plan);
            Http::assertSent(fn(ClientRequest $request)=>$request['order']['invoice_number']===$order->order_id && $request['order']['amount']===$amount);
        }
        $this->assertSame(59000,app(PackageAccess::class)->forType('retail','premium')->price_monthly);
    }

    public function test_admin_variant_configuration_is_separate_from_global_and_other_business_types(): void
    {
        $owner=$this->owner('retail');
        $base=Package::where('code','premium')->firstOrFail(); $before=$base->getAttributes();
        $data=['business_type'=>'retail','name'=>'Premium Retail','price_monthly'=>69000,'price_yearly'=>690000,'is_active'=>1,
            'max_products'=>300,'max_users'=>10,'max_tables'=>null,'export_excel'=>1,'export_pdf'=>0,'custom_branding'=>1,'ai_agent'=>0,'qr_order'=>1];
        $this->actingAs($owner)->put(route('packages.update',$base),$data)->assertForbidden();
        $admin=User::factory()->create();$admin->assignRole(Role::findOrCreate('Super Admin','web'));
        $this->actingAs($admin)->put(route('packages.update',$base),$data)->assertRedirect(route('packages.index'));
        $this->assertSame($before,$base->fresh()->getAttributes());
        $this->assertSame(69000,app(PackageAccess::class)->forType('retail','premium')->price_monthly);
        $this->assertSame($base->price_monthly,app(PackageAccess::class)->forType('cafe','premium')->price_monthly);
        $this->assertFalse(app(PackageAccess::class)->forType('retail','premium')->qr_order);
        $this->get(route('packages.index'))->assertOk()->assertSee('Premium Retail')->assertSee('Laundry');
        $this->put(route('packages.update',$base),[...$data,'business_type'=>'unknown'])->assertSessionHasErrors('business_type');
    }

    public function test_legacy_default_store_and_global_package_behavior_are_unchanged(): void
    {
        $owner=$this->owner(null);
        $default=Business::findOrFail(1)->getAttributes();
        $this->variant('cafe',['qr_order'=>false,'max_products'=>1,'price_monthly'=>123456], 'free');
        $this->assertTrue(app(PackageAccess::class)->allows('qr_order',$owner->business));
        $this->assertSame(100,app(PackageAccess::class)->limit($owner->business,'products'));
        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertViewIs('subscriptions.legacy');
        $this->get(route('tables.index'))->assertOk();
        $this->assertSame($default,Business::findOrFail(1)->getAttributes());
        $this->assertNull(Business::findOrFail(1)->business_type);
    }

    public function test_expired_typed_premium_falls_back_to_free_without_mutating_membership(): void
    {
        $owner=$this->owner('cafe','premium');$business=$owner->business;
        $business->update(['subscription_ends_at'=>now()->subMinute()]);$before=$business->fresh()->getAttributes();
        $this->assertFalse(app(PackageAccess::class)->allows('qr_order',$business));
        $this->assertFalse(app(PackageAccess::class)->allows('export_excel',$business));
        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertSee('Paket Premium Anda telah berakhir.')->assertSee('Aktifkan Premium Kembali');
        $this->get(route('dashboard'))->assertOk();
        $this->assertSame($before,$business->fresh()->getAttributes());
    }

    public function test_pending_payment_and_disabled_variant_do_not_create_fake_success(): void
    {
        $owner=$this->owner('cafe');$this->variant('cafe',['is_active'=>false]);
        $this->actingAs($owner)->post(route('subscriptions.store'),['billing_cycle'=>'monthly'])->assertSessionHasErrors('payment');
        Http::assertNothingSent();
        $this->variant('cafe',['is_active'=>true,'price_monthly'=>79000,'price_yearly'=>790000]);
        $this->post(route('subscriptions.store'),['billing_cycle'=>'monthly'])->assertRedirect();
        $this->get(route('subscriptions.index'))->assertOk()->assertSee('Lanjutkan Pembayaran')->assertSee('Hemat Rp158.000')->assertDontSee('Batalkan');
        $this->assertSame('free',$owner->business->fresh()->subscription_plan);
    }

    public function test_variant_limits_and_registration_preview_use_same_server_configuration(): void
    {
        $owner=$this->owner('retail');
        $this->variant('retail',['max_products'=>25, 'max_users'=>3], 'free');
        $this->variant('retail',['price_monthly'=>89000,'price_yearly'=>1200000]);
        $this->assertSame(25,app(PackageAccess::class)->limit($owner->business,'products'));
        $this->assertSame(3,app(PackageAccess::class)->limit($owner->business,'users'));
        $preview=app(BusinessModules::class)->offerPreview('retail','premium');
        $this->assertSame(89000,$preview['monthly']);
        $this->assertNotContains('QR Order',$preview['features']);
        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertSee('89.000')->assertDontSee('Hemat Rp');
    }

    public function test_disabled_type_variant_is_rejected_at_public_registration(): void
    {
        $this->variant('cafe',['is_active'=>false]);
        $token=(string)Str::uuid();
        $data=['submission_token'=>$token,'store_name'=>'Cafe','business_type'=>'cafe','store_phone'=>'08123456789',
            'store_email'=>'cafe@example.test','address'=>'Alamat','province'=>'Jawa Barat','city'=>'Bandung',
            'owner_name'=>'Pemilik','owner_username'=>'owner_cafe','owner_email'=>'owner@example.test','owner_phone'=>'08123456789',
            'requested_plan'=>'premium','billing_cycle'=>'monthly','terms'=>1];
        $this->withSession(['store_registration_token'=>$token])->post(route('store-registration.store'),$data)->assertSessionHasErrors('requested_plan');
        $this->assertDatabaseCount('store_registrations',0);
        $this->assertDatabaseCount('subscription_orders',0);
        Http::assertNothingSent();
    }
}

