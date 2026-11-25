<?php
namespace Tests\Feature;
use App\Models\{Business,Category,Outlet,Product,Sale,User};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{DB,Http,Mail,Notification};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class OutletModuleTest extends TestCase {
    use RefreshDatabase;
    private User $owner;private User $cashier;private Outlet $a;private Outlet $b;private Product $product;
    protected function setUp():void {
        parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();config(['premium_features.features.multi_outlet.implemented'=>true,'premium_features.features.multi_outlet.status'=>'available']);
        $b=Business::create(['name'=>'Outlet','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>'retail'])->save();app(TenantContext::class)->set($b);
        $this->owner=User::factory()->create();$this->owner->assignRole(Role::findOrCreate('Atasan','web'));$this->cashier=User::factory()->create();$this->cashier->assignRole(Role::findOrCreate('Kasir','web'));
        foreach([$this->owner,$this->cashier] as $u)foreach(['sales.create','sales.view','settings.edit','reports.view'] as $p)$u->givePermissionTo(Permission::findOrCreate($p,'web'));
        $this->a=Outlet::create(['name'=>'A']);$this->b=Outlet::create(['name'=>'B']);$this->cashier->forceFill(['outlet_id'=>$this->a->id])->save();
        $c=Category::create(['name'=>'Produk','slug'=>'produk']);$this->product=Product::create(['name'=>'Produk','category_id'=>$c->id,'price'=>10000,'stock'=>100]);DB::table('outlet_stocks')->insert(['business_id'=>$b->id,'outlet_id'=>$this->a->id,'product_id'=>$this->product->id,'quantity'=>20]);app(TenantContext::class)->clear();
    }
    private function sale(string $code,?int $outlet):Sale {
        app(TenantContext::class)->set($this->owner->business);$s=Sale::create(['code'=>$code,'sale_date'=>now(),'total'=>10000,'paid'=>10000,'change'=>0,'payment_method'=>'cash','payment_status'=>'PAID']);DB::table('sales')->where('id',$s->id)->update(['outlet_id'=>$outlet]);app(TenantContext::class)->clear();return $s;
    }
    public function test_owner_creates_assigns_and_switches_outlets():void {
        $this->actingAs($this->owner)->post(route('outlets.store'),['name'=>'C','is_active'=>1,'business_id'=>1])->assertRedirect();$this->assertDatabaseHas('outlets',['name'=>'C','business_id'=>$this->owner->business_id]);
        $this->patch(route('outlets.assign',$this->cashier),['outlet_id'=>$this->b->id])->assertRedirect();$this->assertEquals($this->b->id,$this->cashier->fresh()->outlet_id);
        $this->post(route('outlets.select'),['outlet_id'=>$this->a->id])->assertRedirect(route('outlets.index'));$this->get(route('outlets.index'))->assertOk();
        $this->post(route('sales.store'),['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','payment_method'=>'cash','paid'=>10000,'outlet_id'=>$this->b->id,'products'=>[['product_id'=>$this->product->id,'quantity'=>1]]])->assertRedirect(route('sales.index'));
        $this->assertDatabaseHas('sales',['business_id'=>$this->owner->business_id,'outlet_id'=>$this->a->id]);
    }
    public function test_assigned_cashier_cannot_read_other_outlet_or_switch():void {
        $a=$this->sale('OUTLET-A',$this->a->id);$b=$this->sale('SECRET-B',$this->b->id);$legacy=$this->sale('UNASSIGNED',null);
        $this->actingAs($this->cashier)->withSession(['outlet.'.$this->owner->business_id=>$this->b->id])->get(route('sales.show',$b))->assertNotFound();
        $this->get(route('sales.show',$a))->assertOk();$this->get(route('sales.show',$legacy))->assertNotFound();
        $this->post(route('outlets.select'),['outlet_id'=>$this->b->id])->assertForbidden();
        $response=$this->get(route('premium.dashboard'))->assertOk();$this->assertSame(1,$response->viewData('report')['count']);
        $csv=$this->get(route('premium.csv'))->assertOk()->streamedContent();$this->assertStringContainsString('OUTLET-A',$csv);$this->assertStringNotContainsString('SECRET-B',$csv);
    }
    public function test_other_tenant_outlet_and_user_are_rejected():void {
        $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$o=Outlet::create(['name'=>'Secret']);$u=User::factory()->create();app(TenantContext::class)->clear();
        $this->actingAs($this->owner)->post(route('outlets.select'),['outlet_id'=>$o->id])->assertNotFound();
        $this->patch(route('outlets.assign',$u),['outlet_id'=>$this->a->id])->assertNotFound();
        $this->patch(route('outlets.assign',$this->cashier),['outlet_id'=>$o->id])->assertNotFound();
    }
    public function test_legacy_and_free_access_and_existing_null_data_remain_unchanged():void {
        $s=$this->sale('OLD',null);$before=$s->fresh()->getAttributes();$this->actingAs($this->owner)->get(route('outlets.index'))->assertOk();$this->assertSame($before,$s->fresh()->getAttributes());
        $this->owner->business->update(['subscription_plan'=>'free']);$this->getJson(route('outlets.index'))->assertForbidden();
        $this->owner->business->forceFill(['business_type'=>null,'subscription_plan'=>'premium'])->save();$this->getJson(route('outlets.index'))->assertForbidden();$this->get(route('sales.show',$s))->assertOk();
    }
    public function test_transfer_is_idempotent_and_never_changes_total_stock():void {
        config(['premium_features.features.inter_outlet_stock_transfer.implemented'=>true,'premium_features.features.inter_outlet_stock_transfer.status'=>'available']);
        $this->actingAs($this->owner);$data=['product_id'=>$this->product->id,'from_outlet_id'=>null,'to_outlet_id'=>$this->b->id,'quantity'=>30,'request_key'=>(string)\Illuminate\Support\Str::uuid()];
        $this->post(route('outlets.inventory.store'),$data)->assertRedirect();$this->post(route('outlets.inventory.store'),$data)->assertRedirect();$this->assertDatabaseCount('outlet_stock_transfers',1);$this->assertSame(100,$this->product->fresh()->stock);
        $this->assertDatabaseHas('outlet_stocks',['outlet_id'=>$this->b->id,'quantity'=>30]);
        $data['from_outlet_id']=$this->b->id;$data['to_outlet_id']=$this->a->id;$data['quantity']=10;$data['request_key']=(string)\Illuminate\Support\Str::uuid();
        $this->post(route('outlets.inventory.store'),$data)->assertRedirect();$this->assertDatabaseHas('outlet_stocks',['outlet_id'=>$this->a->id,'quantity'=>30]);$this->assertSame(100,$this->product->fresh()->stock);
        $this->get(route('outlets.inventory.index'))->assertOk();
        $data['quantity']=100;$data['request_key']=(string)\Illuminate\Support\Str::uuid();$this->post(route('outlets.inventory.store'),$data)->assertSessionHasErrors('products');
        $this->assertDatabaseCount('outlet_stock_transfers',2);
    }
    public function test_sale_consumes_only_assigned_stock_and_cannot_oversell():void {
        $this->actingAs($this->cashier);$data=['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','payment_method'=>'cash','paid'=>300000,'products'=>[['product_id'=>$this->product->id,'quantity'=>21]]];
        $this->post(route('sales.store'),$data)->assertSessionHas('error');$this->assertDatabaseCount('sales',0);$this->assertSame(100,$this->product->fresh()->stock);
        $data['products'][0]['quantity']=2;$this->post(route('sales.store'),$data)->assertRedirect(route('sales.index'));$this->assertSame(98,$this->product->fresh()->stock);$this->assertDatabaseHas('outlet_stocks',['outlet_id'=>$this->a->id,'quantity'=>18]);$this->assertDatabaseHas('outlet_stock_movements',['source_type'=>'sale','quantity'=>-2]);
    }
    public function test_purchase_increases_outlet_and_total_stock_and_other_tenant_transfer_fails():void {
        config(['premium_features.features.inter_outlet_stock_transfer.implemented'=>true,'premium_features.features.inter_outlet_stock_transfer.status'=>'available']);
        $this->owner->givePermissionTo(Permission::findOrCreate('purchases.create','web'));$this->actingAs($this->owner);$supplier=\App\Models\Supplier::create(['name'=>'Supplier']);
        $this->post(route('outlets.select'),['outlet_id'=>$this->a->id]);$this->post(route('purchases.store'),['supplier_id'=>$supplier->id,'purchase_date'=>now()->toDateString(),'products'=>[['product_id'=>$this->product->id,'quantity'=>5,'price'=>1000]]])->assertRedirect();
        $this->assertSame(105,$this->product->fresh()->stock);$this->assertDatabaseHas('outlet_stocks',['outlet_id'=>$this->a->id,'quantity'=>25]);$this->assertDatabaseHas('purchases',['outlet_id'=>$this->a->id]);
        $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$outlet=Outlet::create(['name'=>'Secret']);app(TenantContext::class)->clear();
        $this->post(route('outlets.inventory.store'),['product_id'=>$this->product->id,'to_outlet_id'=>$outlet->id,'quantity'=>1,'request_key'=>(string)\Illuminate\Support\Str::uuid()])->assertNotFound();
    }
    public function test_approved_refund_restores_outlet_allocation_once():void {
        $this->owner->givePermissionTo(Permission::findOrCreate('sales.approve-refunds','web'));
        $this->actingAs($this->cashier)->post(route('sales.store'),['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','payment_method'=>'cash','paid'=>20000,'products'=>[['product_id'=>$this->product->id,'quantity'=>2]]])->assertRedirect(route('sales.index'));
        $sale=Sale::firstOrFail();
        $this->post(route('sale-approvals.store'),['sale_id'=>$sale->id,'type'=>'refund','reason'=>'Refund barang rusak'])->assertRedirect();$a=\App\Models\SaleApproval::firstOrFail();
        $data=['decision'=>'approve','cash_returned'=>1,'review_note'=>'Uang dikembalikan'];$this->actingAs($this->owner)->patch(route('sale-approvals.review',$a),$data)->assertRedirect();
        $this->assertSame(100,$this->product->fresh()->stock);$this->assertDatabaseHas('outlet_stocks',['outlet_id'=>$this->a->id,'quantity'=>20]);$this->assertDatabaseHas('outlet_stock_movements',['source_type'=>'sale_return','quantity'=>2]);
        $this->patch(route('sale-approvals.review',$a),$data)->assertStatus(409);$this->assertSame(100,$this->product->fresh()->stock);
    }
    public function test_central_sale_cannot_consume_stock_reserved_to_outlets():void {
        $this->actingAs($this->owner)->post(route('sales.store'),['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','payment_method'=>'cash','paid'=>1000000,'products'=>[['product_id'=>$this->product->id,'quantity'=>81]]])->assertSessionHas('error');
        $this->assertSame(100,$this->product->fresh()->stock);$this->assertDatabaseCount('sales',0);$this->assertDatabaseHas('outlet_stocks',['outlet_id'=>$this->a->id,'quantity'=>20]);
    }
    public function test_deactivating_selected_outlet_does_not_lock_owner_out():void {
        $this->actingAs($this->owner)->post(route('outlets.select'),['outlet_id'=>$this->b->id])->assertRedirect();
        $this->put(route('outlets.update',$this->b),['name'=>'B','is_active'=>0])->assertRedirect();
        $this->get(route('outlets.index'))->assertOk()->assertViewHas('selected',null);
        $this->put(route('outlets.update',$this->a),['name'=>'A','is_active'=>0])->assertSessionHasErrors('is_active');
        $this->assertTrue($this->a->fresh()->is_active);
    }
    public function test_existing_super_admin_selected_tenant_checkout_is_preserved():void {
        $admin=User::factory()->create();$admin->assignRole(Role::findOrCreate('Super Admin','web'));$admin->givePermissionTo('sales.create');
        $this->actingAs($admin)->withSession(['super_admin_business_id'=>$this->owner->business_id])->post(route('sales.store'),['sale_date'=>now()->toDateString(),'customer_name'=>'Budi','payment_method'=>'cash','paid'=>10000,'products'=>[['product_id'=>$this->product->id,'quantity'=>1]]])->assertRedirect(route('sales.index'));
        $this->assertDatabaseHas('sales',['business_id'=>$this->owner->business_id,'user_id'=>$admin->id,'total'=>10000]);
    }
}
