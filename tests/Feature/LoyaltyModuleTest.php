<?php
namespace Tests\Feature;
use App\Models\{Business,Category,Customer,Product,Sale,Setting,User};
use App\Services\LoyaltyService;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{DB,Http,Mail,Notification};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class LoyaltyModuleTest extends TestCase {
    use RefreshDatabase;
    private User $owner;
    private Product $product;
    protected function setUp(): void {
        parent::setUp(); Http::preventStrayRequests(); Mail::fake(); Notification::fake();
        config(['premium_features.features.customer_loyalty.implemented'=>true,'premium_features.features.customer_loyalty.status'=>'available']);
        $business=Business::create(['name'=>'Loyalty','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);
        $business->forceFill(['business_type'=>'retail'])->save(); app(TenantContext::class)->set($business);
        $this->owner=User::factory()->create();$this->owner->assignRole(Role::findOrCreate('Atasan','web'));
        foreach(['sales.create','sales.edit','sales.delete','customers.view','settings.edit'] as $p) $this->owner->givePermissionTo(Permission::findOrCreate($p,'web'));
        $category=Category::create(['name'=>'Barang','slug'=>'barang']);
        $this->product=Product::create(['name'=>'Barang','category_id'=>$category->id,'price'=>100000,'stock'=>100]);
        Setting::setValue('loyalty_configuration',json_encode(['loyalty_enabled'=>true,'spend_per_point'=>10000,'point_value'=>1000,'minimum_redeem_points'=>5]));
        app(TenantContext::class)->clear(); $this->actingAs($this->owner);
    }
    private function pay(int $points=0,int $paid=100000) {
        return $this->post(route('sales.store'),['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','loyalty_customer_id'=>$points ? Customer::where('name','Budi')->value('id') : null,'payment_method'=>'cash','paid'=>$paid,'redeem_points'=>$points,'products'=>[['product_id'=>$this->product->id,'quantity'=>1]]]);
    }
    public function test_paid_sale_earns_points_and_redeem_is_calculated_on_server(): void {
        $this->pay()->assertRedirect(route('sales.index')); $customer=Customer::firstOrFail();
        $this->assertSame(10,app(LoyaltyService::class)->balance($this->owner->business_id,$customer->id));
        $this->pay(5,95000)->assertRedirect(route('sales.index'));
        $sale=Sale::latest('id')->firstOrFail();$this->assertEquals(95000,$sale->total);$this->assertEquals(5000,$sale->loyalty_discount);
        $this->assertSame(14,app(LoyaltyService::class)->balance($this->owner->business_id,$customer->id));
        app(LoyaltyService::class)->settle($sale);
        $this->assertSame(14,app(LoyaltyService::class)->balance($this->owner->business_id,$customer->id));
        $this->get(route('loyalty.show',$customer))->assertOk()->assertSee('Redeem');
        $this->get(route('loyalty.index'))->assertOk();
    }
    public function test_insufficient_points_roll_back_sale_and_stock(): void {
        $this->pay(10)->assertRedirect(); $this->assertDatabaseCount('sales',0);
        $this->assertSame(100,$this->product->fresh()->stock); $this->assertDatabaseCount('customer_loyalty_points',0);
    }
    public function test_pending_does_not_award_and_paid_transition_is_idempotent(): void {
        app(TenantContext::class)->set($this->owner->business);
        $c=Customer::create(['name'=>'Pending']);
        $sale=Sale::create(['code'=>'PENDING-1','sale_date'=>now(),'total'=>100000,'paid'=>0,'change'=>0,'customer_id'=>$c->id,'payment_status'=>'PENDING']);
        $this->assertSame(0,app(LoyaltyService::class)->balance($this->owner->business_id,$c->id));
        $sale->update(['payment_status'=>'PAID']);$sale->update(['payment_status'=>'PAID']);
        $this->assertSame(10,app(LoyaltyService::class)->balance($this->owner->business_id,$c->id));
        app(TenantContext::class)->clear();
    }
    public function test_other_tenant_points_and_free_access_are_blocked(): void {
        $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$customer=Customer::create(['name'=>'Secret']);app(TenantContext::class)->clear();
        $this->get(route('loyalty.show',$customer))->assertNotFound();
        $this->owner->business->update(['subscription_plan'=>'free']);$this->getJson(route('loyalty.index'))->assertForbidden();
        $this->owner->business->update(['subscription_plan'=>'premium']);$this->owner->revokePermissionTo('settings.edit');
        $this->put(route('loyalty.update'),[])->assertForbidden();
    }
    public function test_settings_and_financial_history_are_protected(): void {
        $this->put(route('loyalty.update'),['loyalty_enabled'=>1,'spend_per_point'=>0,'point_value'=>1,'minimum_redeem_points'=>1])->assertSessionHasErrors('spend_per_point');
        $this->pay(); $sale=Sale::firstOrFail();
        $this->delete(route('sales.destroy',$sale))->assertUnprocessable();
        $this->put(route('sales.update',$sale),[])->assertUnprocessable();
    }
    public function test_forged_customer_for_redeem_cannot_use_other_tenant_balance():void {
        $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$c=Customer::create(['name'=>'Budi']);app(TenantContext::class)->clear();
        DB::table('customer_loyalty_points')->insert(['business_id'=>$other->id,'customer_id'=>$c->id,'points'=>100,'source_type'=>'earned','source_id'=>9999,'description'=>'Saldo tenant lain','created_at'=>now()]);
        $this->post(route('sales.store'),['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','loyalty_customer_id'=>$c->id,'redeem_points'=>10,'payment_method'=>'cash','paid'=>100000,'products'=>[['product_id'=>$this->product->id,'quantity'=>1]]])->assertSessionHas('error');
        $this->assertSame(100,app(LoyaltyService::class)->balance($other->id,$c->id));$this->assertDatabaseCount('sales',0);
    }
}
