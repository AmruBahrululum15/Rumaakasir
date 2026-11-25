<?php

namespace Tests\Feature;

use App\Models\{Business, Conversation, CustomerAccount, User};
use App\Services\{CustomerChat, CustomerPortal};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http, Mail, Notification, Storage};
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\{Permission, Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
    }

    private function account(): CustomerAccount
    {
        return CustomerAccount::create(['name'=>'Portal Customer','email'=>uniqid().'@example.test','phone'=>'+628'.random_int(100000000,999999999),'password'=>'secret-password']);
    }

    private function store(string $name, ?float $latitude = null): Business
    {
        $store=Business::create(['name'=>$name,'subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);
        $store->forceFill(['business_type'=>'retail','latitude'=>$latitude,'longitude'=>$latitude === null ? null : 106.8])->save();
        return $store;
    }

    private function addressData(): array
    {
        return ['label'=>'Rumah','recipient_name'=>'Pelanggan','phone'=>'08123456789','address'=>'Jalan Contoh 12','province'=>'DKI Jakarta','city'=>'Jakarta'];
    }

    public function test_dashboard_profile_and_directory_load_the_interactive_components(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect();
        $this->get(route('customer.stores'))->assertOk()->assertSee('js/customer-portal.js')->assertSee('js/customer-map.js')->assertDontSee('id="chat-launcher"',false);
        $this->actingAs($this->account(),'customer')->get(route('customer.dashboard'))->assertOk()->assertSee('Reservasi Mendatang')->assertSee('css/customer-portal.css')->assertSee('js/customer-floating-chat.js');
        $this->get(route('customer.profile'))->assertOk()->assertSee('Alamat Saya');
        $this->get(route('customer.chat.index'))->assertOk()->assertDontSee('id="chat-launcher"',false);
    }

    public function test_nearby_search_orders_and_filters_coordinates_and_excludes_ineligible_stores(): void
    {
        $near=$this->store('Near',-6.2);$far=$this->store('Far',-6.4);$unknown=$this->store('Unknown');
        $free=$this->store('Free',-6.2);$free->update(['subscription_plan'=>'free']);
        $this->get(route('customer.stores',['latitude'=>-6.2,'longitude'=>106.8,'radius'=>5]))->assertOk()->assertViewHas('stores',fn($s)=>$s->pluck('id')->all()===[$near->id])->assertViewHas('markers',fn($m)=>count($m)===1 && $m[0]['distance']===0.0);
        $this->get(route('customer.stores',['latitude'=>-6.2,'longitude'=>106.8]))->assertOk()->assertViewHas('stores',fn($s)=>$s->pluck('id')->all()===[$near->id,$far->id,$unknown->id]);
        $this->getJson(route('customer.stores',['latitude'=>91,'longitude'=>106.8]))->assertUnprocessable()->assertJsonValidationErrors('latitude');
        $this->getJson(route('customer.stores',['latitude'=>-6.2]))->assertUnprocessable()->assertJsonValidationErrors('longitude');
    }

    public function test_addresses_are_private_and_changing_default_preserves_one_default(): void
    {
        $owner=$this->account();$other=$this->account();
        $this->actingAs($owner,'customer')->post(route('customer.addresses.save'),$this->addressData())->assertRedirect();
        $first=$owner->addresses()->firstOrFail();$this->assertTrue($first->is_default);
        $this->post(route('customer.addresses.save'),array_replace($this->addressData(),['label'=>'Kantor','is_default'=>true]))->assertRedirect();
        $this->assertFalse($first->fresh()->is_default);$this->assertEquals(1,$owner->addresses()->where('is_default',true)->count());
        $this->actingAs($other,'customer')->post(route('customer.addresses.save',$first->id),array_replace($this->addressData(),['city'=>'Changed']))->assertNotFound();
        $this->assertEquals('Jakarta',$first->fresh()->city);
        $this->get(route('customer.profile'))->assertDontSee('Jalan Contoh 12');
    }

    public function test_profile_validates_identity_and_serves_only_own_avatar(): void
    {
        Storage::fake('local');$owner=$this->account();$other=$this->account();
        $this->actingAs($owner,'customer')->patch(route('customer.profile'),['name'=>'Updated','phone'=>$owner->phone,'email'=>$other->email])->assertSessionHasErrors('email');
        $this->patch(route('customer.profile'),['name'=>'Updated','phone'=>$other->phone])->assertSessionHasErrors('phone');
        $this->patch(route('customer.profile'),['name'=>'Updated','phone'=>$owner->phone,'birth_date'=>now()->addDay()->toDateString()])->assertSessionHasErrors('birth_date');
        $this->patch(route('customer.profile'),['name'=>'Updated','phone'=>$owner->phone,'avatar'=>UploadedFile::fake()->image('avatar.png')])->assertSessionHasNoErrors()->assertRedirect();
        $this->get(route('customer.avatar'))->assertOk()->assertHeader('Cache-Control','no-store, private');
        $this->actingAs($other,'customer')->get(route('customer.avatar'))->assertNotFound();
    }

    public function test_chat_json_does_not_leak_other_customer_conversations_and_respects_expiry(): void
    {
        $store=$this->store('Chat Store');$owner=$this->account();$other=$this->account();
        app(CustomerPortal::class)->join($store,$owner);
        $chat=Conversation::create(['business_id'=>$store->id,'customer_account_id'=>$owner->id]);
        app(CustomerChat::class)->send($chat,$owner,'Private message');
        $this->actingAs($owner,'customer')->getJson(route('customer.chat.show',$chat))->assertOk()->assertJsonPath('conversations.0.writable',true)->assertJsonPath('messages.0.message','Private message');
        $chat->messages()->create(['sender_type'=>'staff','sender_id'=>1,'message'=>'Staff reply']);
        $this->getJson(route('customer.chat.index'))->assertOk()->assertJsonPath('conversations.0.unread',1);
        $this->getJson(route('customer.chat.show',$chat))->assertOk()->assertJsonPath('conversations.0.unread',0);
        $chat->update(['status'=>'closed']);
        $this->getJson(route('customer.chat.show',$chat))->assertOk()->assertJsonPath('conversations.0.writable',false);
        $chat->update(['status'=>'open']);
        $this->actingAs($other,'customer')->getJson(route('customer.chat.index'))->assertOk()->assertJsonCount(0,'conversations');
        $this->getJson(route('customer.chat.show',$chat))->assertNotFound();
        $store->update(['subscription_ends_at'=>now()->subDay()]);
        $this->actingAs($owner,'customer')->getJson(route('customer.chat.show',$chat))->assertOk()->assertJsonPath('conversations.0.writable',false);
        $this->postJson(route('customer.chat.send',$chat),['message'=>'New'])->assertForbidden();
    }

    public function test_location_updates_only_the_authorized_staff_business(): void
    {
        $store=$this->store('Own');$other=$this->store('Other');
        app(TenantContext::class)->set($store);$staff=User::factory()->create();app(TenantContext::class)->clear();
        $staff->assignRole(Role::findOrCreate('Atasan','web'));$staff->givePermissionTo(Permission::findOrCreate('settings.edit','web'));
        $this->actingAs($staff)->put(route('settings.location'),['latitude'=>-6.2,'longitude'=>106.8,'business_id'=>$other->id])->assertRedirect();
        $this->assertEquals(-6.2,$store->fresh()->latitude);$this->assertNull($other->fresh()->latitude);
        $this->putJson(route('settings.location'),['latitude'=>-6.2])->assertUnprocessable();
        $staff->revokePermissionTo('settings.edit');
        $this->put(route('settings.location'),['latitude'=>0,'longitude'=>0])->assertForbidden();
    }

    public function test_maps_link_resolves_coordinates_and_preserves_location_on_invalid_links(): void
    {
        $store = $this->store('Own'); $other = $this->store('Other');
        app(TenantContext::class)->set($store); $staff = User::factory()->create(); app(TenantContext::class)->clear();
        $staff->assignRole(Role::findOrCreate('Atasan', 'web'));
        $staff->givePermissionTo(Permission::findOrCreate('settings.edit', 'web'));
        $this->actingAs($staff)->put(route('settings.location'), ['maps_url'=>'https://www.google.com/maps/place/Store/@-6.1,106.7,15z/data=!3d-6.2!4d106.8', 'business_id'=>$other->id])->assertSessionHasNoErrors();
        $this->assertEquals(-6.2, $store->fresh()->latitude);
        $this->assertEquals(106.8, $store->fresh()->longitude);
        $this->assertNull($other->fresh()->latitude);
        $this->putJson(route('settings.location'), ['maps_url'=>'https://example.com/?q=-6.4,106.6'])->assertUnprocessable()->assertJsonValidationErrors('maps_url');
        $this->assertEquals(-6.2, $store->fresh()->latitude);
        Http::assertNothingSent();
        $this->put(route('settings.location'), ['maps_url'=>''])->assertSessionHasNoErrors();
        $this->assertNull($store->fresh()->latitude);
        $this->assertNull($store->fresh()->longitude);
    }

    public function test_maps_short_links_are_resolved_and_unsafe_redirects_or_outages_are_rejected(): void
    {
        Http::fake(['https://maps.app.goo.gl/valid'=>Http::response('', 302, ['Location'=>'https://www.google.com/maps?q=-6.25,106.85']), 'https://maps.app.goo.gl/unsafe'=>Http::response('', 302, ['Location'=>'http://127.0.0.1/private']), 'https://maps.app.goo.gl/down'=>Http::response('', 503)]);
        $service = app(\App\Services\GoogleMapsLocation::class);
        $this->assertSame(['latitude'=>-6.25, 'longitude'=>106.85], $service->coordinates('https://maps.app.goo.gl/valid'));
        foreach (['https://maps.app.goo.gl/unsafe', 'https://maps.app.goo.gl/down', 'https://www.google.com/maps?q=91,106', 'https://www.google.com.evil.test/maps?q=-6,106'] as $url) {
            try { $service->coordinates($url); $this->fail('Invalid location must be rejected.'); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('maps_url', $e->errors()); }
        }
        Http::assertSentCount(3);
    }

    public function test_customer_store_displays_only_its_saved_location_and_hides_map_when_unset(): void
    {
        $store=$this->store('Own location'); $other=$this->store('Other location', -7.1);
        app(TenantContext::class)->set($store); $staff=User::factory()->create(); app(TenantContext::class)->clear();
        $staff->assignRole(Role::findOrCreate('Atasan','web'));
        $staff->givePermissionTo(Permission::findOrCreate('settings.edit','web'));
        $this->actingAs($staff)->put(route('settings.location'), ['maps_url'=>'https://www.google.com/maps?q=-6.25,106.85'])->assertSessionHasNoErrors();
        auth('web')->logout();
        $this->get(route('customer.store',$store->slug))->assertOk()
            ->assertSee('id="store-location"',false)->assertSee('Petunjuk arah')->assertSee('js/customer-map.js')
            ->assertViewHas('markers',fn($markers)=>count($markers)===1 && $markers[0]['name']===$store->name && $markers[0]['latitude']===-6.25 && $markers[0]['longitude']===106.85);
        $this->get(route('customer.store',$other->slug))->assertOk()
            ->assertViewHas('markers',fn($markers)=>count($markers)===1 && $markers[0]['name']===$other->name && $markers[0]['latitude']===-7.1);
        app(TenantContext::class)->clear();
        $store->refresh()->forceFill(['latitude'=>null,'longitude'=>null])->save();
        $this->get(route('customer.store',$store->slug))->assertOk()->assertDontSee('id="store-location"',false)->assertViewHas('markers',[]);
    }
}
