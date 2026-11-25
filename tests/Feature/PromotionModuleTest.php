<?php
namespace Tests\Feature;
use App\Models\{Business,Category,Product,Promotion,Sale,User};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http,Mail,Notification};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class PromotionModuleTest extends TestCase {
    use RefreshDatabase;
    private User $owner; private Product $product;
    protected function setUp(): void {
        parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();
        config(['premium_features.features.promotions.implemented'=>true,'premium_features.features.promotions.status'=>'available']);
        $b=Business::create(['name'=>'Promo','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>'retail'])->save();app(TenantContext::class)->set($b);
        $this->owner=User::factory()->create();$this->owner->assignRole(Role::findOrCreate('Atasan','web'));
        foreach(['settings.edit','sales.create'] as $p)$this->owner->givePermissionTo(Permission::findOrCreate($p,'web'));
        $c=Category::create(['name'=>'Produk','slug'=>'produk']);$this->product=Product::create(['name'=>'Produk','category_id'=>$c->id,'price'=>100000,'stock'=>100]);app(TenantContext::class)->clear();$this->actingAs($this->owner);
    }
    private function promo(array $extra=[]): Promotion { return Promotion::create(array_replace(['name'=>'Promo','code'=>'HEMAT','discount_type'=>'percentage','discount_value'=>10,'min_transaction'=>10000,'start_at'=>now()->subDay(),'end_at'=>now()->addDay(),'is_active'=>true],$extra)); }
    private function pay(?string $code='HEMAT',int $paid=90000) {return $this->post(route('sales.store'),['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','payment_method'=>'cash','paid'=>$paid,'voucher_code'=>$code,'promotion_discount'=>999999,'products'=>[['product_id'=>$this->product->id,'quantity'=>1]]]);}
    public function test_discount_is_server_calculated_and_usage_limit_is_enforced(): void {
        $p=$this->promo(['usage_limit'=>1]);$this->pay()->assertRedirect(route('sales.index'));$sale=Sale::firstOrFail();$this->assertEquals(90000,$sale->total);$this->assertEquals(10000,$sale->promotion_discount);
        $this->pay()->assertSessionHas('error');$this->assertDatabaseCount('sales',1);$this->assertDatabaseCount('promotion_usages',1);
        $this->get(route('promotions.history',$p))->assertOk()->assertSee('10.000');
    }
    public function test_expired_inactive_minimum_and_customer_limit_reject(): void {
        $p=$this->promo(['end_at'=>now()->subMinute()]);$this->pay()->assertSessionHas('error');
        $p->update(['end_at'=>now()->addDay(),'is_active'=>false]);$this->pay()->assertSessionHas('error');
        $p->update(['is_active'=>true,'min_transaction'=>200000]);$this->pay()->assertSessionHas('error');
        $p->update(['min_transaction'=>0,'per_customer_limit'=>1]);$this->pay()->assertRedirect(route('sales.index'));$this->pay()->assertSessionHas('error');$this->assertDatabaseCount('sales',1);
    }
    public function test_best_automatic_promo_and_maximum_discount(): void {
        $this->promo(['code'=>null,'discount_value'=>50,'max_discount'=>7000]);$this->promo(['code'=>null,'discount_type'=>'fixed','discount_value'=>12000]);
        $this->pay(null,88000)->assertRedirect(route('sales.index'));$this->assertEquals(88000,Sale::firstOrFail()->total);
    }
    public function test_cross_tenant_and_free_and_permission_are_blocked(): void {
        $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$p=$this->promo();app(TenantContext::class)->clear();
        $this->pay()->assertSessionHas('error');$this->get(route('promotions.edit',$p))->assertNotFound();
        $this->owner->business->update(['subscription_plan'=>'free']);$this->getJson(route('promotions.index'))->assertForbidden();
        $this->owner->business->update(['subscription_plan'=>'premium']);$this->owner->revokePermissionTo('settings.edit');$this->get(route('promotions.index'))->assertForbidden();
    }
    public function test_crud_validates_and_can_disable(): void {
        $this->get(route('promotions.index'))->assertOk();$p=$this->promo();$data=$p->only(['name','code','discount_type','discount_value','min_transaction','start_at','end_at']);
        $this->put(route('promotions.update',$p),$data+['is_active'=>0])->assertRedirect(route('promotions.index'));$this->assertFalse($p->fresh()->is_active);
        $this->post(route('promotions.store'),array_replace($data,['code'=>'NEW','discount_value'=>101,'is_active'=>1]))->assertSessionHasErrors('discount_value');
    }
    public function test_quote_uses_server_price_and_creates_no_sale_or_usage():void {
        $this->promo();
        $this->postJson(route('premium.checkout.quote'),['products'=>[['product_id'=>$this->product->id,'quantity'=>1,'price'=>1]],'customer_name'=>'Budi','voucher_code'=>'HEMAT','payment_method'=>'cash','business_id'=>1,'total'=>1])->assertOk()->assertJsonPath('total',90000)->assertJsonPath('promotion_discount',10000);
        $this->assertDatabaseCount('sales',0);$this->assertDatabaseCount('promotion_usages',0);$this->assertDatabaseCount('customers',0);
        $this->owner->business->update(['subscription_plan'=>'free']);$this->postJson(route('premium.checkout.quote'),[])->assertForbidden();
    }
}
