<?php
namespace Tests\Feature;
use App\Models\{Business,BusinessCustomer,Conversation,Customer,CustomerAccount,CustomerLinkInvitation,User};
use App\Services\{CustomerIdentity,CustomerPortal};
use App\Notifications\CustomerLinkConfirmation;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Auth,DB,Hash,Http,Mail,Notification};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CustomerPlatformAccessTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp():void {parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();}
    private function business(string $plan='premium'):Business
    {
        Auth::shouldUse('web');$b=Business::create(['name'=>'Toko '.uniqid(),'subscription_plan'=>$plan,'subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>'retail'])->save();return $b;
    }
    private function account(string $phone='081234567890'):CustomerAccount
    {
        return CustomerAccount::create(['name'=>'Customer '.uniqid(),'email'=>uniqid().'@example.test','phone'=>$phone,'password'=>'secret-customer']);
    }
    private function staff(Business $b,string $role='Atasan'):User
    {
        $user=app(CustomerPortal::class)->within($b,fn()=>User::factory()->create());$user->assignRole(Role::findOrCreate($role,'web'));
        foreach(['customers.view','customers.edit'] as $key)$user->givePermissionTo(Permission::findOrCreate($key,'web'));return $user;
    }
    public function test_login_with_phone_normalization_email_and_last_login():void
    {
        $account=$this->account();$this->get(route('customer.login'))->assertOk()->assertSee('Email atau Nomor HP')->assertSee('Daftar Sekarang')->assertSee('Masuk ke Rumaakasir Bisnis');
        foreach(['+62 812-3456-7890','081234567890',strtoupper($account->email)] as $identifier){
            $this->post(route('customer.login.submit'),['identifier'=>$identifier,'password'=>'secret-customer'])->assertRedirect(route('customer.dashboard'));
            $this->assertAuthenticatedAs($account,'customer');$this->assertGuest('web');$this->assertNotNull($account->fresh()->last_login_at);
            $this->post(route('customer.logout'));
        }
        $this->post(route('customer.login.submit'),['identifier'=>$account->phone,'password'=>'wrong'])->assertSessionHasErrors('identifier');
    }
    public function test_ambiguous_legacy_phone_does_not_choose_or_modify_account():void
    {
        $a=$this->account('081234567890');$b=$this->account('+62 812 3456 7890');
        DB::table('customer_accounts')->whereIn('id',[$a->id,$b->id])->update(['login_phone'=>null]);$before=DB::table('customer_accounts')->orderBy('id')->get()->toJson();
        $this->post(route('customer.login.submit'),['identifier'=>'6281234567890','password'=>'secret-customer'])->assertSessionHasErrors('identifier');$this->assertGuest('customer');
        $this->assertSame($before,DB::table('customer_accounts')->orderBy('id')->get()->toJson());
        $this->post(route('customer.login.submit'),['identifier'=>$a->email,'password'=>'secret-customer'])->assertRedirect();$this->assertAuthenticatedAs($a,'customer');
    }
    public function test_legacy_unique_phone_login_and_inactive_account_rejection():void
    {
        $account=$this->account('(0812) 3456-7890');DB::table('customer_accounts')->where('id',$account->id)->update(['login_phone'=>null]);
        $this->post(route('customer.login.submit'),['identifier'=>'+6281234567890','password'=>'secret-customer'])->assertRedirect();$this->assertAuthenticatedAs($account,'customer');
        $this->post(route('customer.logout'));$account->forceFill(['status'=>'inactive'])->save();
        $this->post(route('customer.login.submit'),['identifier'=>$account->email,'password'=>'secret-customer'])->assertSessionHasErrors('identifier');$this->assertGuest('customer');
    }
    public function test_register_rejects_duplicate_normalized_phone_and_hashes_password():void
    {
        $this->account();$data=['name'=>'New Customer','email'=>'new@example.test','phone'=>'+62 81234567890','password'=>'strong-new-password','password_confirmation'=>'strong-new-password'];
        $this->post(route('customer.register.submit'),$data)->assertSessionHasErrors('phone');
        $this->post(route('customer.register.submit'),array_replace($data,['phone'=>'081299988877']))->assertRedirect();
        $account=CustomerAccount::where('email',$data['email'])->firstOrFail();$this->assertTrue(Hash::check($data['password'],$account->password));$this->assertSame('6281299988877',$account->login_phone);
    }
    public function test_staff_and_customer_sessions_do_not_grant_each_others_access():void
    {
        $account=$this->account();$staff=$this->staff($this->business());
        $this->actingAs($account,'customer');Auth::shouldUse('web');$this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('super-admin.platform-customers.index'))->assertRedirect(route('login'));
        auth('customer')->logout();$this->actingAs($staff,'web')->get(route('customer.dashboard'))->assertRedirect(route('customer.login'));
        $this->post(route('customer.login.submit'),['identifier'=>$account->email,'password'=>'secret-customer'])->assertRedirect();$this->assertAuthenticatedAs($staff,'web');$this->assertAuthenticatedAs($account,'customer');
        $this->post(route('customer.logout'));$this->assertAuthenticatedAs($staff,'web');$this->assertGuest('customer');
    }
    public function test_super_admin_directory_contains_counts_and_only_chat_metadata():void
    {
        $business=$this->business();$other=$this->business();$admin=$this->staff($business,'Super Admin');$account=$this->account();
        app(CustomerPortal::class)->join($business,$account);app(CustomerPortal::class)->join($other,$account);
        $chat=Conversation::create(['business_id'=>$business->id,'customer_account_id'=>$account->id,'last_message_at'=>now()]);$chat->messages()->create(['sender_type'=>'customer','sender_id'=>$account->id,'message'=>'PRIVATE-CHAT-BODY']);
        $response=$this->actingAs($admin,'web')->get(route('super-admin.platform-customers.index'))->assertOk()->assertSee($account->email)->assertDontSee('PRIVATE-CHAT-BODY');
        $row=$response->viewData('accounts')->first();$this->assertEquals(2,$row->memberships_count);$this->assertEquals(1,$row->conversations_count);$this->assertEquals(0,$row->orders_count);$this->assertEquals(0,$row->bookings_count);
        $this->get(route('super-admin.platform-customers.show',$account))->assertOk()->assertSee($business->name)->assertSee($other->name)->assertDontSee('PRIVATE-CHAT-BODY');
        $this->withSession(['super_admin_business_id'=>$business->id])->get(route('customer-chat.show',$chat))->assertForbidden();
    }
    public function test_store_directory_only_contains_its_members_and_counts():void
    {
        $a=$this->business();$b=$this->business();$owner=$this->staff($a);$one=$this->account();$two=$this->account('081111111111');
        app(CustomerPortal::class)->join($a,$one);app(CustomerPortal::class)->join($b,$one);app(CustomerPortal::class)->join($b,$two);
        Conversation::create(['business_id'=>$b->id,'customer_account_id'=>$one->id]);
        $response=$this->actingAs($owner,'web')->get(route('portal-customers.index',['business_id'=>$b->id]))->assertOk()->assertSee($one->email)->assertDontSee($two->email)->assertDontSee($b->name);
        $this->assertEquals(1,$response->viewData('accounts')->first()->memberships_count);$this->assertEquals(0,$response->viewData('accounts')->first()->conversations_count);
        $this->get(route('super-admin.platform-customers.index'))->assertForbidden();$this->get(route('super-admin.platform-customers.show',$two))->assertForbidden();
        $cashier=$this->staff($a,'Kasir');$this->actingAs($cashier,'web')->get(route('portal-customers.index'))->assertForbidden();
        $a->update(['subscription_plan'=>'free']);$this->actingAs($owner,'web')->get(route('portal-customers.index'))->assertForbidden();
    }
    public function test_qr_link_requires_staff_review_mail_token_and_customer_confirmation():void
    {
        $business=$this->business();$owner=$this->staff($business);$account=$this->account();
        $legacy=app(CustomerPortal::class)->within($business,fn()=>Customer::create(['name'=>'QR Customer','phone'=>$account->phone,'address'=>$account->email]));$before=$legacy->fresh()->getRawOriginal();
        $this->actingAs($owner,'web')->post(route('portal-customers.invite'),['customer_id'=>$legacy->id,'email'=>$account->email,'business_id'=>1])->assertRedirect();$this->assertDatabaseCount('business_customers',0);
        $token=null;Notification::assertSentTo($account,CustomerLinkConfirmation::class,function($notification)use(&$token){$token=$notification->token;return true;});
        $invite=CustomerLinkInvitation::firstOrFail();$this->assertNotSame($token,$invite->token_hash);$this->assertSame(hash('sha256',$token),$invite->token_hash);
        $this->get(route('customer.links.show',$token))->assertRedirect(route('customer.login'));
        $this->post(route('customer.login.submit'),['identifier'=>$account->email,'password'=>'secret-customer'])->assertRedirect(route('customer.links.show',$token));
        $this->get(route('customer.links.show',$token))->assertOk();$this->assertDatabaseCount('business_customers',0);
        $this->post(route('customer.links.accept',$token),['confirm'=>'1'])->assertRedirect(route('customer.loyalty'));
        $this->assertDatabaseHas('business_customers',['business_id'=>$business->id,'customer_id'=>$legacy->id,'customer_account_id'=>$account->id]);
        $this->assertSame($before,$legacy->fresh()->getRawOriginal());$this->post(route('customer.links.accept',$token),['confirm'=>'1'])->assertGone();
    }
    public function test_qr_link_rejects_cross_tenant_name_only_wrong_account_and_expiry():void
    {
        $business=$this->business();$foreign=$this->business();$owner=$this->staff($business);$account=$this->account();$other=$this->account('081111111111');
        $foreignCustomer=app(CustomerPortal::class)->within($foreign,fn()=>Customer::create(['name'=>$account->name,'phone'=>$account->phone]));
        $local=app(CustomerPortal::class)->within($business,fn()=>Customer::create(['name'=>$account->name,'phone'=>'089999999999']));
        $this->actingAs($owner,'web')->post(route('portal-customers.invite'),['customer_id'=>$foreignCustomer->id,'email'=>$account->email])->assertNotFound();
        $this->post(route('portal-customers.invite'),['customer_id'=>$local->id,'email'=>$account->email])->assertSessionHasErrors('email');
        $local->update(['phone'=>$account->phone]);$this->post(route('portal-customers.invite'),['customer_id'=>$local->id,'email'=>$account->email])->assertRedirect();
        $token=null;Notification::assertSentTo($account,CustomerLinkConfirmation::class,function($n)use(&$token){$token=$n->token;return true;});
        $this->actingAs($other,'customer')->post(route('customer.links.accept',$token),['confirm'=>'1'])->assertNotFound();
        CustomerLinkInvitation::query()->update(['expires_at'=>now()->subMinute()]);$this->actingAs($account,'customer')->post(route('customer.links.accept',$token),['confirm'=>'1'])->assertGone();$this->assertDatabaseCount('business_customers',0);
    }
}
