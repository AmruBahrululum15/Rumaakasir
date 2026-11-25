<?php
namespace Tests\Feature;
use App\Models\{Business,Customer,Sale,User};
use App\Tenancy\TenantContext;
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class CustomerDirectoryTest extends TestCase {
 use RefreshDatabase;
 public function test_all_transaction_sources_are_listed_searchable_and_tenant_scoped():void {
  $business=Business::create(['name'=>'Own','subscription_plan'=>'premium']);app(TenantContext::class)->set($business);
  $user=User::factory()->create();$user->assignRole(Role::findOrCreate('Atasan','web'));$user->givePermissionTo(Permission::findOrCreate('customers.view','web'));
  foreach(['cashier'=>'Kasir Customer','table_qr'=>'QR Customer','customer_portal'=>'Portal Customer','reservation'=>'Reservation Customer'] as $source=>$name){
   $customer=Customer::create(['name'=>$name]);
   Sale::withoutEvents(fn()=>(new Sale(['code'=>'TEST-'.$source,'sale_date'=>today(),'customer_id'=>$customer->id,'user_id'=>$user->id,'total'=>10000,'paid'=>10000,'change'=>0,'payment_method'=>'cash','payment_status'=>'PAID','order_source'=>$source,]))->forceFill(['business_id'=>$business->id])->save());
  }
  $other=Business::create(['name'=>'Other']);app(TenantContext::class)->set($other);$foreign=Customer::create(['name'=>'Private Customer']);app(TenantContext::class)->clear();
  $this->actingAs($user)->get(route('customers.index'))->assertOk()->assertSee('Kasir Customer')->assertSee('QR Customer')->assertSee('Portal Customer')->assertSee('Reservation Customer')->assertDontSee('Private Customer')->assertViewHas('summary',fn($s)=>$s['total']===4&&$s['transacting']===4)->assertViewHas('customers',fn($rows)=>$rows->firstWhere('name','Kasir Customer')->cashier_count===1);
  $this->get(route('customers.index',['search'=>'Portal']))->assertOk()->assertSee('Portal Customer')->assertDontSee('Kasir Customer');
  $this->get(route('customers.show',$foreign))->assertNotFound();
 }
}
