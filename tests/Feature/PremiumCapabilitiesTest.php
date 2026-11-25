<?php

namespace Tests\Feature;

use App\Models\{Business, Category, Customer, Package, PackageVariant, Product, Sale, SaleDetail, User};
use App\Services\{PackageAccess, PackageSnapshot, PremiumFeatures};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{DB, Http, Mail, Notification};
use Illuminate\Support\Str;
use Spatie\Permission\Models\{Permission, Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class PremiumCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp(); Http::preventStrayRequests(); Mail::fake(); Notification::fake();
    }

    private function owner(?string $type = 'retail', string $plan = 'premium'): User
    {
        $business=Business::create(['name'=>'Toko '.($type ?? 'Legacy'),'subscription_plan'=>$plan,'subscription_ends_at'=>now()->addMonth()]);
        $business->forceFill(['business_type'=>$type])->save();
        app(TenantContext::class)->set($business);
        try {
            $owner=User::factory()->create();$owner->assignRole(Role::findOrCreate('Atasan','web'));
            $owner->givePermissionTo(Permission::findOrCreate('reports.view','web'));
            return $owner;
        } finally { app(TenantContext::class)->clear(); }
    }

    private function sale(User $owner, int $total, string $status='PAID', string $label='Produk A'): Sale
    {
        app(TenantContext::class)->set($owner->business);
        try {
            $category=Category::firstOrCreate(['name'=>'Kategori '.$label],['slug'=>Str::uuid()->toString()]);
            $product=Product::firstOrCreate(['name'=>$label],['category_id'=>$category->id,'price'=>5000,'stock'=>20,'status'=>'active']);
            $customer=Customer::firstOrCreate(['name'=>'Customer '.$label]);
            $sale=Sale::create(['code'=>'SALE-'.Str::random(15),'sale_date'=>now()->toDateString(),'total'=>$total,'paid'=>$total,'change'=>0,
                'payment_status'=>$status,'payment_method'=>'cash','user_id'=>$owner->id,'customer_id'=>$customer->id]);
            SaleDetail::create(['sale_id'=>$sale->id,'product_id'=>$product->id,'quantity'=>$total/5000,'price'=>5000,'subtotal'=>$total]);
            return $sale;
        } finally { app(TenantContext::class)->clear(); }
    }

    public function test_dashboard_metrics_use_paid_sales_and_never_other_tenant_data(): void
    {
        $owner=$this->owner();$other=$this->owner('cafe');
        $this->sale($owner,10000);$this->sale($owner,20000);
        $this->sale($owner,500000,'PENDING');$this->sale($owner,500000,'FAILED');
        $this->sale($other,900000,'PAID','SECRET TENANT');
        $response=$this->actingAs($owner)->get(route('premium.dashboard',['business_id'=>$other->business_id]))->assertOk()->assertDontSee('SECRET TENANT');
        $report=$response->viewData('report');
        $this->assertSame(30000.0,$report['revenue']);$this->assertSame(2,$report['count']);
        $this->assertSame(15000.0,$report['average']);$this->assertSame(1,$report['activeCustomers']);
        $this->assertEquals(6,$report['products']->first()->quantity);
        $this->assertEquals(30000,$report['categories']->first()->revenue);
        $this->assertEquals(2,$report['hours']->sum('transactions'));
        $this->get(route('premium.reports'))->assertOk()->assertSee('Penjualan per kasir')->assertSee('Pembelian per supplier');
    }

    public function test_date_ranges_are_validated_and_filter_all_sales_aggregates(): void
    {
        $owner=$this->owner();$sale=$this->sale($owner,10000);
        $sale->update(['sale_date'=>now()->subDays(10)->toDateString()]);
        $response=$this->actingAs($owner)->get(route('premium.reports',['period'=>'daily']))->assertOk();
        $this->assertSame(0,$response->viewData('report')['count']);
        $this->get(route('premium.reports',['period'=>'custom','start'=>'bad','end'=>'2026-09-27']))->assertSessionHasErrors('start');
        $this->get(route('premium.reports',['period'=>'custom','start'=>'2020-01-01','end'=>'2026-01-01']))->assertSessionHasErrors('end');
        $this->get(route('premium.reports',['period'=>'weekly']))->assertOk();
    }

    public function test_dashboard_low_stock_and_latest_purchases_are_tenant_scoped(): void
    {
        $owner=$this->owner(); $other=$this->owner();
        $this->sale($owner,10000,'PAID','Kopi'); $this->sale($other,10000,'PAID','Rahasia');
        DB::table('products')->where('business_id',$owner->business_id)->update(['stock'=>2]);
        DB::table('products')->where('business_id',$other->business_id)->update(['stock'=>1]);
        foreach ([$owner,$other] as $user) {
            app(TenantContext::class)->set($user->business);
            $supplier=\App\Models\Supplier::create(['name'=>'Supplier '.$user->id]);
            \App\Models\Purchase::create(['code'=>'BUY-'.$user->id,'supplier_id'=>$supplier->id,'purchase_date'=>now()->toDateString(),'total'=>100]);
        }
        app(TenantContext::class)->clear();
        $report=$this->actingAs($owner)->get(route('premium.dashboard'))->assertOk()->assertDontSee('Rahasia')->viewData('report');
        $this->assertSame(['Kopi'],$report['lowStock']->pluck('name')->all());
        $this->assertSame(['BUY-'.$owner->id],$report['recentPurchases']->pluck('code')->all());
        $this->assertSame(['BUY-'.$owner->id],$report['purchases']->pluck('code')->all());
        $this->assertSame(['Kopi'],$report['stock']->pluck('name')->all());
        $this->assertEquals(2,$report['stock']->first()->outgoing);
        $this->assertEquals(0,$report['stock']->first()->incoming);
        $this->get(route('premium.reports',['period'=>'custom','start'=>'2020-01-01','end'=>'2020-01-02']))->assertOk()
            ->assertViewHas('report',fn ($r)=>$r['purchases']->isEmpty() && $r['stock']->sum('outgoing')===0);
    }

    public function test_csv_is_scoped_streamed_and_formula_safe(): void
    {
        $owner=$this->owner();$other=$this->owner();
        $sale=$this->sale($owner,10000);$sale->update(['code'=>'=HYPERLINK(test)']);
        $secret=$this->sale($other,900000,'PAID','SECRET');
        $response=$this->actingAs($owner)->get(route('premium.csv',['business_id'=>$other->business_id]))->assertOk();
        $csv=$response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK(test)",$csv);
        $this->assertStringNotContainsString($secret->code,$csv);
        $this->assertStringNotContainsString('900000',$csv);
        $this->assertStringContainsString('10000',$csv);
        $stock=$this->get(route('premium.csv',['report'=>'stock']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Produk A',$stock);
        $this->assertStringNotContainsString('SECRET',$stock);
        $this->assertStringStartsWith("\xEF\xBB\xBF",$stock);
        $this->get(route('premium.csv',['report'=>'purchases']))->assertOk();
        $empty=$this->get(route('premium.csv',['period'=>'custom','start'=>'2020-01-01','end'=>'2020-01-02']))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('HYPERLINK',$empty);
        $this->getJson(route('premium.csv',['report'=>'users']))->assertUnprocessable();
    }

    public function test_free_and_expired_cannot_access_new_premium_features(): void
    {
        $owner=$this->owner('cafe','free');
        foreach (['premium.dashboard','premium.reports','premium.csv'] as $route) {
            $this->actingAs($owner)->get(route($route))->assertRedirect(route('subscriptions.index'));
            $this->getJson(route($route))->assertForbidden();
        }
        $owner->business->update(['subscription_plan'=>'premium','subscription_ends_at'=>now()->subMinute()]);
        $this->getJson(route('premium.dashboard'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_permissions_still_apply_for_premium_accounts(): void
    {
        $owner=$this->owner();$owner->revokePermissionTo('reports.view');
        $this->actingAs($owner)->get(route('premium.dashboard'))->assertForbidden();
        $this->get(route('premium.csv'))->assertForbidden();
    }

    public function test_unimplemented_business_features_are_never_available_even_with_forged_settings(): void
    {
        foreach (['retail'=>'stock_opname','laundry'=>'laundry_delivery','bengkel'=>'service_orders','salon'=>'staff_commissions'] as $type=>$key) {
            $owner=$this->owner($type);
            PackageVariant::create(['package_id'=>Package::where('code','premium')->firstOrFail()->id,'business_type'=>$type,'settings'=>['feature_statuses'=>[$key=>'available']]]);
            $catalog=app(PremiumFeatures::class)->catalog($type);
            $this->assertSame('disabled',$catalog[$key]['status']);
            $this->assertFalse(app(PackageAccess::class)->allows($key,$owner->business));
        }
        $this->assertArrayNotHasKey('qr_order',app(PremiumFeatures::class)->catalog('retail'));
        $this->assertArrayHasKey('booking',app(PremiumFeatures::class)->catalog('laundry'));
    }

    public function test_admin_status_validation_and_cache_invalidation(): void
    {
        $owner=$this->owner();$package=Package::where('code','premium')->firstOrFail();
        $this->assertTrue(app(PackageAccess::class)->allows('advanced_dashboard',$owner->business));
        $variant=PackageVariant::create(['package_id'=>$package->id,'business_type'=>'retail','settings'=>['feature_statuses'=>['advanced_dashboard'=>'disabled']]]);
        $this->assertFalse(app(PackageAccess::class)->allows('advanced_dashboard',$owner->business));
        $variant->update(['settings'=>['feature_statuses'=>['advanced_dashboard'=>'available']]]);
        $this->assertTrue(app(PackageAccess::class)->allows('advanced_dashboard',$owner->business));
        $admin=User::factory()->create();$admin->assignRole(Role::findOrCreate('Super Admin','web'));
        $this->actingAs($admin)->get(route('packages.index'))->assertOk()->assertSee('name="feature_statuses[advanced_dashboard]"', false)->assertDontSee('name="feature_statuses[0]"', false);
        $payload=['business_type'=>'retail',...$package->only(['name',...Package::LIMITS,...Package::FEATURES]),'feature_statuses'=>['stock_opname'=>'available']];
        $this->actingAs($admin)->put(route('packages.update',$package),$payload)->assertSessionHasErrors('feature_statuses.stock_opname');
        $payload['feature_statuses']=['advanced_dashboard'=>'disabled'];
        $this->put(route('packages.update',$package),$payload)->assertRedirect(route('packages.index'));
        $this->assertFalse(app(PackageAccess::class)->allows('advanced_dashboard',$owner->business));
    }

    public function test_catalog_reads_package_tables_only_once_per_request_scope(): void
    {
        app(PackageSnapshot::class)->clear();$queries=0;
        DB::listen(function ($query) use (&$queries) { if(preg_match('/from ["`]?package(s|_variants)["`]?/i',$query->sql)) $queries++; });
        for($i=0;$i<20;$i++) {app(PremiumFeatures::class)->catalog('cafe');app(PremiumFeatures::class)->catalog('retail');}
        $this->assertSame(2,$queries);
    }

    public function test_legacy_sidebar_package_limits_and_default_store_remain_unchanged(): void
    {
        $owner=$this->owner(null,'free');$default=Business::findOrFail(1)->getAttributes();
        $before=$owner->business->getAttributes();
        $this->assertTrue(app(PackageAccess::class)->allows('qr_order',$owner->business));
        $this->assertSame(100,app(PackageAccess::class)->limit($owner->business,'products'));
        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertDontSee('Advanced Dashboard')->assertDontSee('Advanced Reports');
        $this->get(route('subscriptions.index'))->assertOk()->assertViewIs('subscriptions.legacy');
        $this->assertSame($before,$owner->business->fresh()->getAttributes());
        $this->assertSame($default,Business::findOrFail(1)->getAttributes());
    }

    public function test_subscription_separates_available_and_upcoming_and_reuses_type_price(): void
    {
        $owner=$this->owner('cafe');$base=Package::where('code','premium')->firstOrFail();
        PackageVariant::create(['package_id'=>$base->id,'business_type'=>'cafe','settings'=>['price_monthly'=>79000,'price_yearly'=>790000]]);
        $response=$this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertSee('Tersedia sekarang')->assertDontSee('Segera Hadir')->assertSee('79.000')->assertSee('Advanced Dashboard');
        $available=array_column($response->viewData('comparison'),'key');
        $this->assertContains('qr_order',$available);$this->assertContains('customer_loyalty',$available);
        $this->assertContains('multi_outlet',$available);$this->assertContains('booking',$available);
    }
}

