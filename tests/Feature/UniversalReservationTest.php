<?php
namespace Tests\Feature;
use App\Models\{Booking,BookingPayment,BookingResource,BookingStaffSchedule,Business,BusinessService,Customer,Setting,User};
use App\Services\{BookingService,ReservationSchedule};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http,Mail};
use Illuminate\Support\Str;
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class UniversalReservationTest extends TestCase {
 use RefreshDatabase;
 private Business $business; private User $owner; private Customer $customer; private BusinessService $service;
 protected function setUp():void {
  parent::setUp();Http::preventStrayRequests();Mail::fake();
  $this->business=Business::create(['name'=>'Cafe Reservasi','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);
  $this->business->forceFill(['business_type'=>'cafe'])->save();app(TenantContext::class)->set($this->business);
  $this->owner=User::factory()->create();$this->owner->assignRole(Role::findOrCreate('Atasan','web'));
  foreach(['sales.create','settings.edit'] as $p)$this->owner->givePermissionTo(Permission::findOrCreate($p,'web'));
  $this->customer=Customer::create(['name'=>'Amru']);$this->service=BusinessService::create(['name'=>'Meja Cafe','kind'=>'appointment','unit'=>'session','price'=>100000,'duration_minutes'=>60]);
  app(TenantContext::class)->clear();$this->actingAs($this->owner);
 }
 private function data(array $extra=[]):array {return array_replace(['customer_id'=>$this->customer->id,'service_id'=>$this->service->id,'booking_date'=>now()->addDay()->toDateString(),'start_time'=>'10:00'],$extra);}
 private function create(array $extra=[]):Booking {$this->post(route('bookings.store'),$this->data($extra))->assertSessionHasNoErrors()->assertRedirect();return Booking::latest('id')->firstOrFail();}
 private function settings(array $extra=[]):void {app(TenantContext::class)->set($this->business);Setting::setValue('reservation_configuration',json_encode($extra));app(TenantContext::class)->clear();}
 public function test_guest_booking_price_token_and_retry_are_server_controlled():void {
  auth('web')->logout();$form=$this->get(route('reservations.form',$this->business->slug))->assertOk();$token=$form->viewData('requestToken');
  $data=$this->data(['request_token'=>$token,'customer_name'=>'Guest','customer_phone'=>'081234567890','total'=>1,'paid'=>100000,'business_id'=>999]);
  $url=route('reservations.status',[$this->business->slug,$token]);
  $this->post(route('reservations.store',$this->business->slug),$data)->assertRedirect($url);
  $this->post(route('reservations.store',$this->business->slug),$data)->assertRedirect($url);
  $b=Booking::withoutGlobalScopes()->firstOrFail();$this->assertEquals(100000,$b->total);$this->assertEquals(0,$b->paid);$this->assertEquals($this->business->id,$b->business_id);$this->assertSame(hash('sha256',$token),$b->public_token_hash);$this->assertDatabaseCount('bookings',1);
  $this->get($url)->assertOk()->assertSee('Guest');$this->getJson($url)->assertJsonPath('status_code','pending');
  $this->get(route('reservations.status',[$this->business->slug,Str::random(48)]))->assertNotFound();
  $this->post(route('reservations.store',$this->business->slug),array_replace($data,['request_token'=>Str::random(48)]))->assertStatus(419);
 }
 public function test_capacity_and_buffer_prevent_overbooking_and_allow_adjacent_slots():void {
  $this->service->update(['booking_capacity'=>2,'buffer_minutes'=>15]);$this->create();$this->create();
  $this->post(route('bookings.store'),$this->data())->assertSessionHasErrors('start_time');
  $this->post(route('bookings.store'),$this->data(['start_time'=>'11:00']))->assertSessionHasErrors('start_time');$this->create(['start_time'=>'11:15']);
 }
 public function test_resource_capacity_is_shared_across_services():void {
  app(TenantContext::class)->set($this->business);$resource=BookingResource::create(['name'=>'Lapangan 1','kind'=>'court','capacity'=>1]);$other=BusinessService::create(['name'=>'Latihan','kind'=>'appointment','unit'=>'session','price'=>1,'duration_minutes'=>30]);app(TenantContext::class)->clear();
  $this->create(['resource_id'=>$resource->id]);$this->post(route('bookings.store'),$this->data(['resource_id'=>$resource->id,'service_id'=>$other->id]))->assertSessionHasErrors('start_time');
  $this->create(['resource_id'=>$resource->id,'service_id'=>$other->id,'start_time'=>'11:00']);
 }
 public function test_operating_hours_holidays_and_employee_schedule_control_slots():void {
  $date=now()->addDay();$this->settings(['opens_at'=>'09:00','closes_at'=>'12:00','slot_minutes'=>30]);
  app(TenantContext::class)->set($this->business);BookingStaffSchedule::create(['staff_id'=>$this->owner->id,'day_of_week'=>$date->dayOfWeek,'opens_at'=>'10:00','closes_at'=>'12:00']);app(TenantContext::class)->clear();
  $url=route('reservations.slots',$this->business->slug);$query=['service_id'=>$this->service->id,'staff_id'=>$this->owner->id,'date'=>$date->toDateString()];
  $this->getJson($url.'?'.http_build_query($query))->assertJsonPath('slots',['10:00','10:30','11:00']);
  $this->post(route('bookings.store'),$this->data(['staff_id'=>$this->owner->id,'start_time'=>'09:00']))->assertSessionHasErrors('staff_id');
  $this->settings(['holidays'=>[$date->toDateString()]]);$this->getJson($url.'?'.http_build_query($query))->assertJsonPath('slots',[]);
  $this->settings(['enabled'=>false]);$this->get(route('reservations.form',$this->business->slug))->assertForbidden();
 }
 public function test_cash_deposit_is_idempotent_and_completion_requires_balance():void {
  $this->settings(['auto_confirm'=>true,'payment_mode'=>'deposit','deposit_percent'=>30]);$b=$this->create();$this->assertSame('awaiting_payment',$b->status);
  $this->patch(route('bookings.status',$b),['status'=>'confirmed'])->assertSessionHasErrors('status');
  $key=(string)Str::uuid();$url=route('bookings.cash',$b);
  $this->post($url,['request_key'=>$key,'amount'=>30000])->assertSessionHasNoErrors();$this->post($url,['request_key'=>$key,'amount'=>30000])->assertSessionHasNoErrors();$this->assertDatabaseCount('booking_payments',1);$this->assertSame('confirmed',$b->fresh()->status);$this->assertEquals(30000,$b->fresh()->paid);
  $this->post($url,['request_key'=>$key,'amount'=>40000])->assertStatus(409);
  $this->post($url,['request_key'=>(string)Str::uuid(),'amount'=>80000])->assertSessionHasErrors('amount');
  $this->travel(2)->days();$this->patch(route('bookings.status',$b),['status'=>'in_progress'])->assertSessionHasNoErrors();$this->assertNotNull($b->fresh()->checked_in_at);
  $this->patch(route('bookings.status',$b),['status'=>'completed'])->assertSessionHasErrors('status');
  $this->post($url,['request_key'=>(string)Str::uuid(),'amount'=>70000])->assertSessionHasNoErrors();$this->patch(route('bookings.status',$b),['status'=>'completed'])->assertSessionHasNoErrors();$this->assertSame('paid',$b->fresh()->payment_status);
 }
 public function test_cross_tenant_details_cash_resources_and_slots_are_rejected():void {
  $b=$this->create();$other=Business::create(['name'=>'Other','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$other->forceFill(['business_type'=>'cafe'])->save();app(TenantContext::class)->set($other);$user=User::factory()->create();$user->assignRole('Atasan');$user->givePermissionTo('sales.create');$resource=BookingResource::create(['name'=>'Secret','kind'=>'room','capacity'=>1]);app(TenantContext::class)->clear();
  $this->post(route('bookings.store'),$this->data(['resource_id'=>$resource->id]))->assertNotFound();
  $this->actingAs($user);$this->get(route('bookings.show',$b))->assertNotFound();$this->post(route('bookings.cash',$b),['request_key'=>(string)Str::uuid(),'amount'=>1])->assertNotFound();$this->get(route('bookings.index'))->assertOk()->assertDontSee($b->code);
 }
 public function test_customer_notification_is_tenant_scoped_and_opens_correct_booking():void {
  $b=$this->create();app(\App\Services\BookingNotifications::class)->send($b->id,'created');
  $note=$this->owner->notifications()->firstOrFail();$this->assertEquals($this->business->id,$note->data['business_id']);$this->assertEquals($b->id,$note->data['booking_id']);
  $this->post(route('notifications.open',$note->id))->assertRedirect(route('bookings.show',$b));$this->assertNotNull($note->fresh()->read_at);
  $this->owner->revokePermissionTo('sales.create');$this->assertEquals(0,app(\App\Services\StaffNotifications::class)->query($this->owner)->count());
 }
 public function test_filtered_history_paginates_and_reschedule_retains_code():void {
  $this->service->update(['booking_capacity'=>100]);for($i=0;$i<21;$i++)$this->create();
  $r=$this->get(route('bookings.index'))->assertOk();$this->assertEquals(20,$r->viewData('bookings')->count());$this->assertEquals(21,$r->viewData('bookings')->total());
  $b=Booking::firstOrFail();$code=$b->code;$this->put(route('bookings.update',$b),$this->data(['capacity_units'=>2,'start_time'=>'12:00']))->assertSessionHasNoErrors();$this->assertSame($code,$b->fresh()->code);
  $r=$this->get(route('bookings.index',['search'=>$code,'status'=>'pending','calendar'=>'month']))->assertOk();$this->assertEquals(1,$r->viewData('bookings')->total());
  $this->get(route('bookings.report',['from'=>now()->toDateString(),'to'=>now()->addWeek()->toDateString()]))->assertOk()->assertDownload('reservasi.csv');
 }
 public function test_cash_checkout_credits_deposit_and_creates_one_pos_sale_without_stock():void {
  $this->settings(['auto_confirm'=>true,'payment_mode'=>'deposit','deposit_percent'=>30]);$b=$this->create();
  $this->post(route('bookings.cash',$b),['request_key'=>(string)Str::uuid(),'amount'=>30000])->assertSessionHasNoErrors();$this->travel(2)->days();
  $url=route('bookings.checkout',$b);$key=(string)Str::uuid();
  $this->post($url,['request_key'=>$key,'cash_received'=>60000])->assertSessionHasErrors('cash_received');
  $this->post($url,['request_key'=>$key,'cash_received'=>80000,'total'=>1,'business_id'=>999])->assertSessionHasNoErrors();
  $this->post($url,['request_key'=>$key,'cash_received'=>80000])->assertSessionHasNoErrors();
  $b->refresh();$this->assertSame('completed',$b->status);$this->assertEquals(100000,$b->paid);$this->assertNotNull($b->sale_id);$this->assertDatabaseCount('sales',1);$this->assertDatabaseCount('sale_details',0);$this->assertDatabaseCount('booking_payments',2);$this->assertEquals(100000,BookingPayment::sum('amount'));
  $sale=\App\Models\Sale::firstOrFail();$this->assertEquals(100000,$sale->total);$this->assertEquals(10000,$sale->change);$this->assertSame('PAID',$sale->payment_status);$this->assertSame('reservation',$sale->order_source);$this->assertEquals($this->business->id,$sale->business_id);
  $this->owner->givePermissionTo(Permission::findOrCreate('sales.view','web'));$this->get(route('sales.receipt',$sale))->assertOk()->assertSee($this->service->name)->assertSee($b->code);
 } public function test_reservation_receipt_prints_actual_balance_and_never_marks_unpaid_as_paid():void {
  $b=$this->create(['notes'=>'Catatan <script>alert(1)</script>']);$url=route('bookings.receipt',$b);
  $this->get(route('bookings.show',$b))->assertOk()->assertSee('Cetak struk reservasi')->assertSee($url,false);
  $this->get($url)->assertOk()->assertSee('STRUK RESERVASI')->assertSee($b->code)->assertSee('Cafe Reservasi')->assertSee('Amru')->assertSee('Meja Cafe')->assertSee('Rp 100.000')->assertSee('Belum dibayar')->assertSee('BUKAN BUKTI PELUNASAN')->assertDontSee('<script>alert(1)</script>',false);
  $this->post(route('bookings.cash',$b),['request_key'=>(string)Str::uuid(),'amount'=>30000])->assertSessionHasNoErrors();
  $this->get(route('bookings.receipt',[$b,'width'=>58]))->assertOk()->assertSee('data-width="58"',false)->assertSee('Sebagian dibayar')->assertSee('Rp 70.000')->assertSee('Penerimaan Cash');
  $this->post(route('bookings.cash',$b),['request_key'=>(string)Str::uuid(),'amount'=>70000])->assertSessionHasNoErrors();
  $this->get($url)->assertOk()->assertSee('PEMBAYARAN LUNAS')->assertDontSee('BUKAN BUKTI PELUNASAN');
  $this->assertSame('pending',$b->fresh()->status);$this->assertDatabaseCount('booking_payments',2);$this->getJson($url.'?width=120')->assertUnprocessable();
 }
 public function test_reservation_receipt_is_private_and_requires_tenant_permission():void {
  $b=$this->create();$url=route('bookings.receipt',$b);
  $this->get($url)->assertOk()->assertHeader('Cache-Control','no-store, private');
  $other=Business::create(['name'=>'Other','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$other->forceFill(['business_type'=>'cafe'])->save();app(TenantContext::class)->set($other);$user=User::factory()->create();$user->assignRole('Atasan');$user->givePermissionTo('sales.create');app(TenantContext::class)->clear();
  $this->actingAs($user)->get($url)->assertNotFound();$this->actingAs($this->owner);$this->owner->revokePermissionTo('sales.create');$this->get($url)->assertForbidden();auth('web')->logout();$this->get($url)->assertRedirect(route('login'));
 }}




