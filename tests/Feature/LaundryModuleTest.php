<?php
namespace Tests\Feature;
use App\Models\{Business,BusinessService,Customer,LaundryOrder,User};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http,Mail,Notification};
use Illuminate\Support\Str;
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class LaundryModuleTest extends TestCase {
    use RefreshDatabase;
    private User $owner;private Customer $customer;private BusinessService $service;
    protected function setUp():void {
        parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();
        config(['premium_features.features.laundry_services.implemented'=>true,'premium_features.features.laundry_services.status'=>'available']);
        $b=Business::create(['name'=>'Laundry','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>'laundry'])->save();app(TenantContext::class)->set($b);
        $this->owner=User::factory()->create();$this->owner->assignRole(Role::findOrCreate('Atasan','web'));foreach(['sales.create','settings.edit'] as $p)$this->owner->givePermissionTo(Permission::findOrCreate($p,'web'));
        $this->customer=Customer::create(['name'=>'Budi']);$this->service=BusinessService::create(['name'=>'Cuci Kilo','kind'=>'laundry','unit'=>'kg','price'=>10000]);app(TenantContext::class)->clear();$this->actingAs($this->owner);
    }
    private function createOrder(array $extra=[]){return $this->post(route('laundry.store'),array_replace(['service_id'=>$this->service->id,'customer_id'=>$this->customer->id,'quantity'=>2.5,'estimated_at'=>now()->addDay()->toDateTimeString(),'total'=>1,'business_id'=>1],$extra));}
    public function test_real_order_payment_and_full_status_timeline():void {
        $this->createOrder()->assertRedirect();$order=LaundryOrder::firstOrFail();$this->assertEquals(25000,$order->total);$this->assertEquals($this->owner->business_id,$order->business_id);
        $this->get(route('laundry.index'))->assertOk();$this->get(route('laundry.show',$order))->assertOk()->assertSee('Cuci Kilo');
        $this->patch(route('laundry.status',$order),['status'=>'ready'])->assertSessionHasErrors('status');
        foreach(['washing','drying','ironing','ready'] as $status)$this->patch(route('laundry.status',$order),compact('status'))->assertRedirect();
        $this->patch(route('laundry.status',$order),['status'=>'picked_up'])->assertSessionHasErrors('status');
        $payment=['request_key'=>(string)Str::uuid(),'amount'=>25000];$this->post(route('laundry.pay',$order),$payment)->assertRedirect();$this->post(route('laundry.pay',$order),$payment)->assertRedirect();
        $this->assertDatabaseCount('laundry_payments',1);$this->assertEquals(25000,$order->fresh()->paid);
        $this->patch(route('laundry.status',$order),['status'=>'picked_up'])->assertRedirect();$this->assertDatabaseCount('laundry_status_histories',6);
    }
    public function test_no_overpayment_and_unit_quantity_requires_integer():void {
        $this->service->update(['unit'=>'unit']);$this->createOrder()->assertSessionHasErrors('quantity');
        $this->createOrder(['quantity'=>2])->assertRedirect();$order=LaundryOrder::firstOrFail();$this->post(route('laundry.pay',$order),['request_key'=>(string)Str::uuid(),'amount'=>20001])->assertSessionHasErrors('amount');$this->assertEquals(0,$order->fresh()->paid);
    }
    public function test_cross_tenant_business_type_and_free_are_blocked():void {
        $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$c=Customer::create(['name'=>'Secret']);app(TenantContext::class)->clear();$this->createOrder(['customer_id'=>$c->id])->assertNotFound();
        $this->owner->business->forceFill(['business_type'=>'retail'])->save();$this->getJson(route('laundry.index'))->assertForbidden();
        $this->owner->business->forceFill(['business_type'=>'laundry','subscription_plan'=>'free'])->save();$this->getJson(route('laundry.index'))->assertForbidden();
    }
    public function test_service_creation_and_permission():void {
        $this->post(route('laundry.services.store'),['name'=>'Setrika','unit'=>'kg','price'=>5000])->assertRedirect();$this->assertDatabaseCount('business_services',2);
        $this->owner->revokePermissionTo('settings.edit');$this->post(route('laundry.services.store'),['name'=>'Tanpa izin','unit'=>'kg','price'=>1])->assertForbidden();
    }
}
