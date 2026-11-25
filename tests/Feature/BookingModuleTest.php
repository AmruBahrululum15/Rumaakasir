<?php
namespace Tests\Feature;
use App\Models\{Booking,Business,BusinessService,Customer,User};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http,Mail,Notification};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class BookingModuleTest extends TestCase {
    use RefreshDatabase;
    private User $owner;private Customer $customer;private BusinessService $service;
    protected function setUp():void {
        parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();
        config(['premium_features.features.booking.implemented'=>true,'premium_features.features.booking.status'=>'available','premium_features.features.booking.types'=>['salon','barbershop','jasa']]);
        $b=Business::create(['name'=>'Salon','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>'salon'])->save();app(TenantContext::class)->set($b);
        $this->owner=User::factory()->create();$this->owner->assignRole(Role::findOrCreate('Atasan','web'));foreach(['sales.create','settings.edit'] as $p)$this->owner->givePermissionTo(Permission::findOrCreate($p,'web'));
        $this->customer=Customer::create(['name'=>'Budi']);$this->service=BusinessService::create(['name'=>'Potong','kind'=>'appointment','unit'=>'session','price'=>50000,'duration_minutes'=>60]);app(TenantContext::class)->clear();$this->actingAs($this->owner);
    }
    private function data(array $extra=[]):array{return array_replace(['customer_id'=>$this->customer->id,'staff_id'=>$this->owner->id,'service_id'=>$this->service->id,'booking_date'=>now()->addDay()->toDateString(),'start_time'=>'10:00','business_id'=>1,'ends_at'=>now()],$extra);}
    public function test_create_conflicts_adjacency_and_cancel_release_slot():void {
        $this->post(route('bookings.store'),$this->data())->assertRedirect();$b=Booking::firstOrFail();$this->assertSame('11:00',$b->ends_at->format('H:i'));$this->assertEquals($this->owner->business_id,$b->business_id);
        $this->get(route('bookings.index'))->assertOk()->assertSee('Potong');$this->get(route('bookings.edit',$b))->assertOk();
        $this->post(route('bookings.store'),$this->data(['start_time'=>'10:30']))->assertSessionHasErrors('start_time');
        $this->post(route('bookings.store'),$this->data(['start_time'=>'11:00']))->assertRedirect();$this->assertDatabaseCount('bookings',2);
        $this->patch(route('bookings.status',$b),['status'=>'cancelled'])->assertRedirect();
        $this->post(route('bookings.store'),$this->data())->assertRedirect();$this->assertDatabaseCount('bookings',3);
    }
    public function test_update_and_status_transitions():void {
        $this->post(route('bookings.store'),$this->data());$b=Booking::firstOrFail();
        $this->put(route('bookings.update',$b),$this->data(['start_time'=>'12:00']))->assertRedirect();$this->assertSame('12:00',$b->fresh()->starts_at->format('H:i'));
        $this->patch(route('bookings.status',$b),['status'=>'completed'])->assertSessionHasErrors('status');
        $this->patch(route('bookings.status',$b),['status'=>'confirmed'])->assertRedirect();
        $this->travel(2)->days();$this->patch(route('bookings.status',$b),['status'=>'completed'])->assertRedirect();
        $this->put(route('bookings.update',$b),$this->data())->assertSessionHasErrors('booking');
    }
    public function test_cross_tenant_staff_customer_and_service_are_rejected():void {
        $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$u=User::factory()->create();$c=Customer::create(['name'=>'Secret']);$s=BusinessService::create(['name'=>'Secret','kind'=>'appointment','unit'=>'session','price'=>1,'duration_minutes'=>60]);app(TenantContext::class)->clear();
        foreach(['staff_id'=>$u->id,'customer_id'=>$c->id,'service_id'=>$s->id] as $key=>$value)$this->post(route('bookings.store'),$this->data([$key=>$value]))->assertNotFound();
        $this->assertDatabaseCount('bookings',0);
    }
    public function test_type_package_permissions_and_unassigned_conflict():void {
        $this->post(route('bookings.store'),$this->data(['staff_id'=>null]))->assertRedirect();$this->post(route('bookings.store'),$this->data(['staff_id'=>null]))->assertSessionHasErrors('start_time');
        $this->owner->business->forceFill(['business_type'=>'retail'])->save();$this->getJson(route('bookings.index'))->assertForbidden();
        $this->owner->business->forceFill(['business_type'=>'jasa','subscription_plan'=>'free'])->save();$this->getJson(route('bookings.index'))->assertForbidden();
        $this->owner->business->update(['subscription_plan'=>'premium']);$this->get(route('bookings.index'))->assertOk();
        $this->owner->revokePermissionTo('sales.create');$this->get(route('bookings.index'))->assertForbidden();
    }
}
