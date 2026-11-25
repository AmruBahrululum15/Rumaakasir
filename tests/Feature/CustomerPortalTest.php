<?php
namespace Tests\Feature;
use App\Models\{Business,BusinessCustomer,BusinessService,Booking,Category,Conversation,CustomerAccount,CustomerLoyaltyPoint,Product,Sale,User};
use App\Services\{CustomerPortal,PortalOrderService};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{DB,Hash,Http,Mail,Notification,Password};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp():void {parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();}
    private function store(string $type='salon',string $plan='premium'):Business {
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        $b=Business::create(['name'=>'Toko '.uniqid(),'subscription_plan'=>$plan,'subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>$type])->save();return $b;
    }
    private function customer():CustomerAccount {return CustomerAccount::create(['name'=>'Pelanggan '.uniqid(),'email'=>uniqid().'@example.test','phone'=>'0812345678','password'=>'secret-password']);}
    private function staff(Business $b):User {
        app(TenantContext::class)->set($b);$u=User::factory()->create();$u->assignRole(Role::findOrCreate('Atasan','web'));
        foreach(['customer_chat.view','customer_chat.reply','sales.view','sales.create'] as $p)$u->givePermissionTo(Permission::findOrCreate($p,'web'));
        app(TenantContext::class)->clear();return $u;
    }
    public function test_account_registration_login_logout_and_staff_guard_separation():void {
        $this->get(route('customer.register'))->assertOk();$this->post(route('customer.register.submit'),['name'=>'Amru','email'=>'amru@example.test','phone'=>'0812345678','password'=>'strong-secret-123','password_confirmation'=>'strong-secret-123'])->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated('customer');$this->assertGuest('web');$this->get(route('customer.dashboard'))->assertOk();$this->patch(route('customer.profile'),['name'=>'Amru Updated','phone'=>'081111111'])->assertRedirect();
        $this->post(route('customer.logout'))->assertRedirect();$this->assertGuest('customer');
        $this->post(route('customer.login.submit'),['email'=>'amru@example.test','password'=>'strong-secret-123'])->assertRedirect(route('customer.dashboard'));
    }
    public function test_password_reset_uses_separate_broker_and_is_one_time():void {
        $a=$this->customer();$b=$this->customer();$staff=$this->staff($this->store());
        $this->post(route('customer.forgot.submit'),['email'=>$a->email])->assertSessionHas('success');
        Notification::assertSentTo($a,\App\Notifications\CustomerPasswordReset::class);
        $token=Password::broker('customers')->createToken($a);
        $data=['email'=>$a->email,'token'=>$token,'password'=>'new-strong-password','password_confirmation'=>'new-strong-password'];
        $this->post(route('customer.reset.submit'),array_replace($data,['email'=>$b->email]))->assertSessionHasErrors();
        $this->post(route('customer.reset.submit'),$data)->assertRedirect(route('customer.login'));$this->assertTrue(Hash::check('new-strong-password',$a->fresh()->password));
        $this->post(route('customer.reset.submit'),$data)->assertSessionHasErrors();$this->assertTrue(Hash::check('secret-password',$b->fresh()->password));$this->assertEquals($staff->password,$staff->fresh()->password);
    }
    public function test_directory_premium_feature_expiry_and_legacy_rules():void {
        $premium=$this->store();$free=$this->store('retail','free');$expired=$this->store();$expired->update(['subscription_ends_at'=>now()->subDay()]);$legacy=$this->store();$legacy->forceFill(['business_type'=>null])->save();
        $this->get(route('customer.stores'))->assertOk()->assertSee($premium->name)->assertDontSee($free->name)->assertDontSee($expired->name)->assertDontSee($legacy->name);
        $this->get(route('customer.store',$premium->slug))->assertOk();
        foreach([$free,$expired,$legacy] as $b)$this->get(route('customer.store',$b->slug))->assertForbidden()->assertSee('Toko sedang tidak menerima layanan online.');
        config(['premium_features.features.customer_portal.status'=>'disabled']);$this->get(route('customer.store',$premium->slug))->assertForbidden();
    }
    public function test_membership_and_loyalty_are_separate_per_store_and_customer():void {
        $a=$this->customer();$b=$this->customer();$one=$this->store();$two=$this->store();$portal=app(CustomerPortal::class);
        $m1=$portal->join($one,$a);$m2=$portal->join($two,$a);$other=$portal->join($one,$b);
        $this->assertNotEquals($m1->customer_id,$m2->customer_id);$this->assertEquals($m1->id,$portal->join($one,$a)->id);
        foreach([[$m1,40],[$m2,120],[$other,999]] as [$m,$points])DB::table('customer_loyalty_points')->insert(['business_id'=>$m->business_id,'customer_id'=>$m->customer_id,'points'=>$points,'source_type'=>'earned','source_id'=>$m->id,'description'=>'POINTS-'.$points,'created_at'=>now()]);
        $this->actingAs($a,'customer')->get(route('customer.loyalty'))->assertOk()->assertSee('40 poin')->assertSee('120 poin')->assertDontSee('POINTS-999');
        $this->assertDatabaseCount('customer_accounts',2);$this->assertDatabaseCount('business_customers',3);
    }
    public function test_booking_conflicts_customer_ownership_cross_tenant_and_expiry():void {
        $store=$this->store('barbershop');$staff=$this->staff($store);$account=$this->customer();
        $service=app(CustomerPortal::class)->within($store,fn()=>BusinessService::create(['name'=>'Potong Rambut','kind'=>'appointment','unit'=>'session','price'=>50000,'duration_minutes'=>60]));
        $data=['service_id'=>$service->id,'staff_id'=>$staff->id,'booking_date'=>now()->addDay()->toDateString(),'start_time'=>'10:00','business_id'=>1,'customer_account_id'=>999];
        $this->actingAs($account,'customer')->post(route('customer.book',$store->slug),$data)->assertRedirect(route('customer.bookings'));
        $booking=Booking::withoutGlobalScopes()->firstOrFail();$this->assertEquals($account->id,$booking->customer_account_id);$this->assertEquals($store->id,$booking->business_id);
        $this->post(route('customer.book',$store->slug),$data)->assertSessionHasErrors('start_time');
        $this->get(route('customer.bookings'))->assertOk()->assertSee('Potong Rambut');
        $other=$this->customer();$this->actingAs($other,'customer')->get(route('customer.bookings'))->assertDontSee('Potong Rambut');$this->patch(route('customer.bookings.cancel',$booking->id))->assertNotFound();
        $foreign=$this->store();$this->post(route('customer.book',$foreign->slug),$data)->assertNotFound();
        $store->update(['subscription_ends_at'=>now()->subDay()]);$this->post(route('customer.book',$store->slug),$data)->assertForbidden();
        $this->actingAs($account,'customer')->get(route('customer.bookings'))->assertSee('Potong Rambut');
    }
    public function test_chat_authorization_staff_reply_notifications_and_expired_read_only():void {
        $store=$this->store();$staff=$this->staff($store);$account=$this->customer();$other=$this->customer();
        $this->actingAs($account,'customer')->post(route('customer.chat.start',$store->slug))->assertRedirect();$chat=Conversation::firstOrFail();
        $this->post(route('customer.chat.send',$chat),['message'=>'Halo <script>alert(1)</script>'])->assertRedirect();Notification::assertSentTo($staff,\App\Notifications\PortalMessage::class);
        $this->get(route('customer.chat.show',$chat))->assertOk()->assertSee('&lt;script&gt;',false);
        $this->actingAs($other,'customer')->get(route('customer.chat.show',$chat))->assertNotFound();$this->post(route('customer.chat.send',$chat),['message'=>'intruder'])->assertNotFound();
        $foreignStaff=$this->staff($this->store());$this->actingAs($foreignStaff,'web')->get(route('customer-chat.show',$chat))->assertNotFound();
        $this->actingAs($staff,'web')->get(route('customer-chat.show',$chat))->assertOk();$this->assertNotNull($chat->messages()->first()->read_at);
        $this->post(route('customer-chat.send',$chat),['message'=>'Selamat datang'])->assertRedirect();Notification::assertSentTo($account,\App\Notifications\PortalMessage::class);
        $store->update(['subscription_ends_at'=>now()->subDay()]);$this->actingAs($account,'customer')->get(route('customer.chat.show',$chat))->assertOk()->assertSee('Selamat datang');$this->post(route('customer.chat.send',$chat),['message'=>'baru'])->assertForbidden();
    }
    public function test_cart_server_price_stock_idempotency_and_staff_settlement():void {
        $store=$this->store('retail');$staff=$this->staff($store);$account=$this->customer();
        $product=app(CustomerPortal::class)->within($store,function(){ $c=Category::create(['name'=>'Barang','slug'=>'barang']);return Product::create(['name'=>'Produk Portal','category_id'=>$c->id,'price'=>25000,'stock'=>10,'is_active'=>true]); });
        $this->actingAs($account,'customer')->post(route('customer.cart.add',$store->slug),['product_id'=>$product->id,'quantity'=>2,'price'=>1])->assertRedirect();$this->get(route('customer.cart',$store->slug))->assertOk();
        $token=session('portal_cart.'.$account->id.'.'.$store->id.'.token');
        $this->post(route('customer.cart.checkout',$store->slug),['token'=>$token,'amount'=>1,'business_id'=>1])->assertRedirect(route('customer.orders.show',Sale::withoutGlobalScopes()->where('customer_account_id',$account->id)->firstOrFail()->id));
        $this->post(route('customer.cart.checkout',$store->slug),['token'=>$token])->assertRedirect(route('customer.orders.show',Sale::withoutGlobalScopes()->where('customer_account_id',$account->id)->firstOrFail()->id));
        $sale=Sale::withoutGlobalScopes()->where('customer_account_id',$account->id)->firstOrFail();$this->assertEquals(50000,$sale->total);$this->assertEquals('CASH_PENDING',$sale->payment_status);$this->assertEquals(8,$product->fresh()->stock);$this->assertDatabaseCount('portal_checkouts',1);
        $this->get(route('customer.orders'))->assertOk()->assertSee($sale->code);
        $this->actingAs($staff,'web')->get(route('portal-orders.index'))->assertOk()->assertSee($sale->code);
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertRedirect();$this->assertEquals('PAID',$sale->fresh()->payment_status);
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertStatus(422);
    }
    public function test_qr_links_only_authenticated_customer_and_keeps_default_business_unchanged():void {
        $default=Business::findOrFail(1)->getRawOriginal();$business=$this->store('cafe');$a=$this->customer();$b=$this->customer();
        [$table,$product]=app(CustomerPortal::class)->within($business,function(){
            $table=\App\Models\Table::create(['name'=>'Meja Portal','qr_token'=>'portal-qr-test','is_active'=>true]);
            $category=Category::create(['name'=>'Menu','slug'=>'menu']);$product=Product::create(['name'=>'Kopi','category_id'=>$category->id,'price'=>20000,'stock'=>10,'is_active'=>true]);return [$table,$product];
        });
        $this->actingAs($a,'customer')->post(route('customer.checkout.store',$table),['customer_name'=>'Guest name','customer_phone'=>'08111111','customer_email'=>'guest@example.test','payment_method'=>'cash','cart'=>json_encode([['id'=>$product->id,'quantity'=>1]]),'customer_account_id'=>$b->id])->assertRedirect();
        $sale=Sale::withoutGlobalScopes()->where('business_id',$business->id)->firstOrFail();$this->assertEquals($a->id,$sale->customer_account_id);
        $this->assertDatabaseHas('business_customers',['business_id'=>$business->id,'customer_id'=>$sale->customer_id,'customer_account_id'=>$a->id]);
        $this->get(route('customer.orders'))->assertSee($sale->code);$this->actingAs($b,'customer')->get(route('customer.orders'))->assertDontSee($sale->code);
        $this->assertSame($default,Business::findOrFail(1)->getRawOriginal());
    }
    public function test_chat_real_unread_notification_and_unprivileged_staff():void {
        Notification::swap(new \Illuminate\Notifications\ChannelManager($this->app));
        $business=$this->store();$staff=$this->staff($business);$account=$this->customer();
        $this->actingAs($account,'customer')->post(route('customer.chat.start',$business->slug));$chat=Conversation::firstOrFail();
        $this->post(route('customer.chat.send',$chat),['message'=>'Pertanyaan pelanggan'])->assertRedirect();$this->assertEquals(1,$staff->unreadNotifications()->count());
        $this->actingAs($staff,'web')->get(route('customer-chat.show',$chat))->assertOk();$this->assertEquals(0,$staff->unreadNotifications()->count());
        $this->post(route('customer-chat.send',$chat),['message'=>'Balasan toko'])->assertRedirect();$this->assertEquals(1,$account->unreadNotifications()->count());
        $this->actingAs($account,'customer')->getJson(route('customer.chat.show',$chat))->assertOk()->assertJsonFragment(['message'=>'Balasan toko']);$this->assertEquals(0,$account->unreadNotifications()->count());
        $staff->revokePermissionTo('customer_chat.reply');$this->actingAs($staff,'web')->post(route('customer-chat.send',$chat),['message'=>'Tidak diizinkan'])->assertForbidden();
        $staff->revokePermissionTo('customer_chat.view');$this->get(route('customer-chat.show',$chat))->assertForbidden();
    }
    public function test_free_and_expired_cannot_start_chat_and_auth_rate_limit():void {
        $account=$this->customer();$free=$this->store('retail','free');$this->actingAs($account,'customer')->post(route('customer.chat.start',$free->slug))->assertForbidden();
        $expired=$this->store();$expired->update(['subscription_ends_at'=>now()->subDay()]);$this->post(route('customer.chat.start',$expired->slug))->assertForbidden();
        $this->assertDatabaseCount('conversations',0);
        for($i=0;$i<5;$i++)$this->post(route('customer.login.submit'),['email'=>'unknown@example.test','password'=>'bad'])->assertRedirect();
        $this->post(route('customer.login.submit'),['email'=>'unknown@example.test','password'=>'bad'])->assertStatus(429);
    }
    public function test_csrf_rejects_public_registration_without_token():void {
        $this->app['env']='local';
        try {$this->post(route('customer.register.submit'),['name'=>'Unsafe'])->assertStatus(419);}
        finally {$this->app['env']='testing';}
        $this->assertDatabaseCount('customer_accounts',0);
    }
    public function test_order_cross_tenant_stock_rollback_cancellation_and_loyalty_settlement():void {
        $business=$this->store('retail');$foreign=$this->store('retail');$staff=$this->staff($business);$foreignStaff=$this->staff($foreign);$account=$this->customer();
        $make=function($b){return app(CustomerPortal::class)->within($b,function(){ $category=Category::create(['name'=>'Barang','slug'=>'barang']);return Product::create(['name'=>'Barang Portal','category_id'=>$category->id,'price'=>20000,'stock'=>5,'is_active'=>true]);});};
        $product=$make($business);$otherProduct=$make($foreign);
        $this->actingAs($account,'customer')->post(route('customer.cart.add',$business->slug),['product_id'=>$otherProduct->id,'quantity'=>1])->assertNotFound();
        $this->post(route('customer.cart.add',$business->slug),['product_id'=>$product->id,'quantity'=>10]);$token=session('portal_cart.'.$account->id.'.'.$business->id.'.token');
        $this->post(route('customer.cart.checkout',$business->slug),['token'=>$token])->assertSessionHasErrors('cart');$this->assertEquals(5,$product->fresh()->stock);
        $this->post(route('customer.cart.add',$business->slug),['product_id'=>$product->id,'quantity'=>1]);$token=session('portal_cart.'.$account->id.'.'.$business->id.'.token');
        $this->post(route('customer.cart.checkout',$business->slug),['token'=>$token])->assertRedirect();$sale=Sale::withoutGlobalScopes()->where('business_id',$business->id)->firstOrFail();
        $this->actingAs($foreignStaff,'web')->patch(route('portal-orders.update',$sale->id),['action'=>'paid'])->assertNotFound();
        $this->actingAs($staff,'web')->patch(route('portal-orders.update',$sale->id),['action'=>'cancelled'])->assertRedirect();$this->assertEquals(5,$product->fresh()->stock);
        $this->patch(route('portal-orders.update',$sale->id),['action'=>'cancelled'])->assertStatus(422);$this->assertEquals(5,$product->fresh()->stock);
        DB::table('settings')->insert(['business_id'=>$business->id,'key'=>'loyalty_configuration','value'=>json_encode(['loyalty_enabled'=>true,'spend_per_point'=>10000,'point_value'=>100,'minimum_redeem_points'=>10])]);
        $new=app(PortalOrderService::class)->create($business,$account,[$product->id=>2],(string)\Illuminate\Support\Str::uuid(),null);
        $this->patch(route('portal-orders.update',$new->id),['action'=>'paid'])->assertRedirect();
        $this->assertEquals(4,app(\App\Services\LoyaltyService::class)->balance($business->id,$new->customer_id));
        $this->assertEquals(0,app(\App\Services\LoyaltyService::class)->balance($foreign->id,$new->customer_id));
    }
    public function test_vouchers_are_eligible_and_only_from_joined_stores():void {
        $business=$this->store();$foreign=$this->store();$account=$this->customer();app(CustomerPortal::class)->join($business,$account);
        $make=function($b,$code,$end){return app(CustomerPortal::class)->within($b,fn()=>\App\Models\Promotion::create(['name'=>$code,'code'=>$code,'discount_type'=>'fixed','discount_value'=>1000,'min_transaction'=>10000,'start_at'=>now()->subDays(2),'end_at'=>$end,'is_active'=>true]));};
        $make($business,'AVAILABLE',now()->addDay());$make($business,'EXPIRED',now()->subDay());$make($foreign,'SECRET-VOUCHER',now()->addDay());
        $this->actingAs($account,'customer')->get(route('customer.vouchers'))->assertOk()->assertSee('AVAILABLE')->assertDontSee('EXPIRED')->assertDontSee('SECRET-VOUCHER');
        config(['premium_features.features.promotions.status'=>'disabled']);$this->get(route('customer.vouchers'))->assertDontSee('AVAILABLE');
    }
    public function test_product_details_and_bengkel_booking_are_business_scoped():void {
        $business=$this->store('bengkel');$other=$this->store('retail');$account=$this->customer();$staff=$this->staff($business);
        $service=app(CustomerPortal::class)->within($business,fn()=>BusinessService::create(['name'=>'Service Ringan','kind'=>'appointment','unit'=>'session','price'=>75000,'duration_minutes'=>60]));
        $this->actingAs($account,'customer')->post(route('customer.book',$business->slug),['service_id'=>$service->id,'staff_id'=>$staff->id,'booking_date'=>now()->addDay()->toDateString(),'start_time'=>'09:00','notes'=>'Kendaraan B 1234 ABC'])->assertRedirect(route('customer.bookings'));
        $product=app(CustomerPortal::class)->within($other,function(){$c=Category::create(['name'=>'Produk','slug'=>'produk']);return Product::create(['name'=>'Barang Rahasia','category_id'=>$c->id,'price'=>10000,'stock'=>5,'is_active'=>true]);});
        $this->get(route('customer.product',[$other->slug,$product->id]))->assertOk()->assertSee('Barang Rahasia');
        $this->get(route('customer.product',[$business->slug,$product->id]))->assertNotFound();
    }
    public function test_paid_staff_purchase_links_explicit_customer_but_never_claims_guest_by_phone():void {
        $business=$this->store('retail');$staff=$this->staff($business);$account=$this->customer();$member=app(CustomerPortal::class)->join($business,$account);
        $make=function($staffId,$code)use($business,$member){return app(CustomerPortal::class)->within($business,fn()=>Sale::create(['code'=>$code,'sale_date'=>now()->toDateString(),'total'=>10000,'paid'=>10000,'change'=>0,'customer_id'=>$member->customer_id,'user_id'=>$staffId,'payment_status'=>'PAID','payment_method'=>'cash']));};
        $guest=$make(null,'GUEST-PRIVATE');$staffSale=$make($staff->id,'STAFF-LINKED');
        $this->assertNull($guest->fresh()->customer_account_id);$this->assertEquals($account->id,$staffSale->fresh()->customer_account_id);
        $this->actingAs($account,'customer')->get(route('customer.orders'))->assertSee('STAFF-LINKED')->assertDontSee('GUEST-PRIVATE');$this->assertNotNull($member->fresh()->last_transaction_at);
    }
    public function test_super_admin_sees_usage_but_cannot_read_private_conversations():void {
        $business=$this->store();$staff=$this->staff($business);$staff->assignRole(Role::findOrCreate('Super Admin','web'));$account=$this->customer();app(CustomerPortal::class)->join($business,$account);
        $chat=Conversation::create(['business_id'=>$business->id,'customer_account_id'=>$account->id]);$chat->messages()->create(['sender_type'=>'customer','sender_id'=>$account->id,'message'=>'PRIVATE MESSAGE']);
        $this->actingAs($staff,'web')->get(route('super-admin.customer-portal'))->assertOk()->assertDontSee('PRIVATE MESSAGE');
        $this->get(route('customer-chat.show',$chat))->assertRedirect(route('businesses.index'));
        $this->withSession(['super_admin_business_id'=>$business->id])->get(route('customer-chat.show',$chat))->assertForbidden();
        $this->post(route('customer-chat.send',$chat),['message'=>'Support without consent'])->assertForbidden();
    }
    public function test_guest_qr_cannot_claim_or_change_registered_customer_by_phone():void {
        $business=$this->store('cafe');$account=$this->customer();$member=app(CustomerPortal::class)->join($business,$account);
        [$table,$product]=app(CustomerPortal::class)->within($business,function(){
            $table=\App\Models\Table::create(['name'=>'Meja Guest','qr_token'=>'guest-isolation','is_active'=>true]);
            $c=Category::create(['name'=>'Menu','slug'=>'menu']);$p=Product::create(['name'=>'Teh','category_id'=>$c->id,'price'=>10000,'stock'=>5,'is_active'=>true]);return [$table,$p];
        });
        $this->post(route('customer.checkout.store',$table),['customer_name'=>'Impersonated','customer_phone'=>$account->phone,'customer_email'=>$account->email,'payment_method'=>'cash','cart'=>json_encode([['id'=>$product->id,'quantity'=>1]])])->assertRedirect();
        $sale=Sale::withoutGlobalScopes()->where('business_id',$business->id)->firstOrFail();
        $this->assertNotEquals($member->customer_id,$sale->customer_id);$this->assertNull($sale->customer_account_id);
        $this->assertDatabaseHas('customers',['id'=>$member->customer_id,'name'=>$account->name]);
    }

    public function test_registration_saves_owned_default_address_and_rejects_incomplete_address(): void
    {
        $data=['name'=>'Alamat Customer','email'=>'alamat@example.test','phone'=>'081299988877','password'=>'strong-secret-123','password_confirmation'=>'strong-secret-123','address'=>'Jl. Contoh No. 12','province'=>'Jawa Barat','city'=>'Kabupaten Bogor','district'=>'Cileungsi','village'=>'Cileungsi','postal_code'=>'16820','customer_account_id'=>999999];
        $this->post(route('customer.register.submit'), array_replace($data,['province'=>'']))->assertSessionHasErrors('province');
        $this->assertDatabaseMissing('customer_accounts',['email'=>$data['email']]);
        $this->post(route('customer.register.submit'), array_replace($data,['postal_code'=>'abc']))->assertSessionHasErrors('postal_code');
        $this->post(route('customer.register.submit'),$data)->assertRedirect(route('customer.dashboard'));
        $account=\App\Models\CustomerAccount::where('email',$data['email'])->firstOrFail();
        $this->assertDatabaseHas('customer_addresses',['customer_account_id'=>$account->id,'label'=>'Rumah','recipient_name'=>$data['name'],'address'=>$data['address'],'province'=>'Jawa Barat','city'=>'Kabupaten Bogor','postal_code'=>'16820','is_default'=>true]);
        $this->assertSame(1,$account->addresses()->count());
        $this->get(route('customer.profile'))->assertOk()->assertSee('Jl. Contoh No. 12');
    }

    public function test_customer_registration_resolves_region_names_and_rejects_cross_region_selection(): void
    {
        $regions=\Mockery::mock(\App\Services\IndonesiaRegions::class)->makePartial();
        $regions->shouldReceive('listing')->with('province',null)->andReturn([['id'=>'32','name'=>'Jawa Barat','postal_codes'=>[]]]);
        $regions->shouldReceive('listing')->with('city','32')->andReturn([['id'=>'32.01','name'=>'Kabupaten Bogor','postal_codes'=>[]]]);
        $regions->shouldReceive('listing')->with('district','32.01')->andReturn([['id'=>'32.01.07','name'=>'Cileungsi','postal_codes'=>[]]]);
        $regions->shouldReceive('listing')->with('village','32.01.07')->andReturn([['id'=>'32.01.07.2001','name'=>'Pasirangin','postal_codes'=>['16820']]]);
        $this->app->instance(\App\Services\IndonesiaRegions::class,$regions);
        $data=['name'=>'Region Customer','email'=>'region@example.test','phone'=>'081299955566','password'=>'strong-secret-123','password_confirmation'=>'strong-secret-123','address'=>'Jl. Contoh','address_mode'=>'structured','province_id'=>'32','city_id'=>'32.01','district_id'=>'32.01.07','village_id'=>'32.01.07.2001','province'=>'Forged','city'=>'Forged','postal_code'=>'16820'];
        $this->post(route('customer.register.submit'),array_replace($data,['city_id'=>'31.01']))->assertSessionHasErrors('city_id');
        $this->assertDatabaseMissing('customer_accounts',['email'=>$data['email']]);
        $this->post(route('customer.register.submit'),array_replace($data,['postal_code'=>'12345']))->assertSessionHasErrors('postal_code');
        $this->post(route('customer.register.submit'),$data)->assertRedirect(route('customer.dashboard'));
        $account=\App\Models\CustomerAccount::where('email',$data['email'])->firstOrFail();
        $this->assertDatabaseHas('customer_addresses',['customer_account_id'=>$account->id,'province'=>'Jawa Barat','city'=>'Kabupaten Bogor','district'=>'Cileungsi','village'=>'Pasirangin','postal_code'=>'16820']);
    }
}

