<?php
namespace Tests\Feature;

use App\Models\{Business,Conversation,CustomerAccount,User};
use App\Services\{CustomerChat,CustomerPortal};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http,Mail};
use Illuminate\Support\Str;
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CustomerChatNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp():void {parent::setUp();Http::preventStrayRequests();Mail::fake();}
    private function store():Business {
        $business=Business::create(['name'=>'Toko '.uniqid(),'subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);
        $business->forceFill(['business_type'=>'retail'])->save();return $business;
    }
    private function account(string $name='Amru'):CustomerAccount {return CustomerAccount::create(['name'=>$name,'email'=>uniqid().'@example.test','phone'=>'08123456789','password'=>'password']);}
    private function staff(Business $business,string $role='Atasan',array $permissions=['customer_chat.view','customer_chat.reply']):User {
        app(TenantContext::class)->set($business);$staff=User::factory()->create();app(TenantContext::class)->clear();
        $staff->assignRole(Role::findOrCreate($role,'web'));
        foreach($permissions as $permission)$staff->givePermissionTo(Permission::findOrCreate($permission,'web'));
        return $staff;
    }
    private function conversation(Business $business,CustomerAccount $account):Conversation {
        app(CustomerPortal::class)->join($business,$account);
        return Conversation::create(['business_id'=>$business->id,'customer_account_id'=>$account->id]);
    }
    public function test_customer_messages_notify_only_authorized_store_roles_with_safe_preview():void {
        $business=$this->store();$other=$this->store();$account=$this->account();$chat=$this->conversation($business,$account);
        $recipients=[$this->staff($business),$this->staff($business,'Admin'),$this->staff($business,'Kasir',['customer_chat.reply'])];
        $excluded=[$this->staff($other),$this->staff($business,'Kasir',['customer_chat.view']),$this->staff($business,'Admin',[]),$this->staff($business,'Auditor')];
        $inactive=$this->staff($business);$inactive->update(['is_active'=>false]);$excluded[]=$inactive;
        $preview='Apakah jam 7 masih tersedia? <script>alert(1)</script>';
        $this->actingAs($account,'customer')->postJson(route('customer.chat.send',$chat),['message'=>$preview,'business_id'=>$other->id,'customer_account_id'=>999])->assertOk();
        foreach($recipients as $staff){
            $note=$staff->unreadNotifications()->sole();$data=$note->data;
            $this->assertEquals('customer_chat',$data['type']);$this->assertEquals($business->id,$data['business_id']);$this->assertEquals($chat->id,$data['conversation_id']);
            $this->assertEquals($account->id,$data['customer_account_id']);$this->assertEquals('Amru',$data['customer_name']);$this->assertEquals($preview,$data['message_preview']);
        }
        foreach($excluded as $staff)$this->assertEquals(0,$staff->notifications()->count());
        $response=$this->actingAs($recipients[0])->getJson(route('notifications.feed'))->assertOk()->assertJsonPath('unread_count',1)->assertJsonPath('chat_unread_count',1);
        $this->assertStringContainsString('&lt;script&gt;',$response->json('html'));$this->assertStringNotContainsString('<script>',$response->json('html'));
        $this->actingAs($recipients[2])->get(route('customer-chat.show',$chat))->assertOk()->assertSee('data-chat-unread',false);
    }
    public function test_opening_notification_reads_only_that_conversation_and_preserves_order_notification():void {
        $business=$this->store();$staff=$this->staff($business);$account=$this->account();$other=$this->account('Budi');
        $first=$this->conversation($business,$account);$second=$this->conversation($business,$other);
        app(CustomerChat::class)->send($first,$account,'Satu');app(CustomerChat::class)->send($first,$account,'Dua');app(CustomerChat::class)->send($second,$other,'Percakapan lain');
        $order=$staff->notifications()->create(['id'=>(string)Str::uuid(),'type'=>'order','data'=>['type'=>'new_customer_order','title'=>'Pesanan baru','business_id'=>$business->id,'order_id'=>123]]);
        $note=$staff->notifications()->where('data->conversation_id',$first->id)->firstOrFail();
        $this->actingAs($staff)->getJson(route('notifications.feed'))->assertJsonPath('unread_count',4)->assertJsonPath('chat_unread_count',3);
        $this->post(route('notifications.open',$note->id))->assertRedirect(route('customer-chat.show',$first));
        $this->get(route('customer-chat.show',$first))->assertOk();
        $this->getJson(route('notifications.feed'))->assertJsonPath('unread_count',2)->assertJsonPath('chat_unread_count',1);
        $this->assertNull($order->fresh()->read_at);$this->assertNull($second->messages()->firstOrFail()->read_at);
        $this->assertEquals(0,$staff->unreadNotifications()->where('data->conversation_id',$first->id)->count());
    }
    public function test_foreign_and_legacy_chat_notifications_cannot_bypass_tenant_or_permission():void {
        $business=$this->store();$foreign=$this->store();$staff=$this->staff($business);$foreignStaff=$this->staff($foreign);$account=$this->account();$chat=$this->conversation($foreign,$account);
        app(CustomerChat::class)->send($chat,$account,'Rahasia toko lain');$note=$foreignStaff->notifications()->firstOrFail();
        $legacy=$staff->notifications()->create(['id'=>(string)Str::uuid(),'type'=>\App\Notifications\PortalMessage::class,'data'=>['conversation_id'=>$chat->id,'message'=>'Legacy secret']]);
        $this->actingAs($staff)->getJson(route('notifications.feed'))->assertJsonPath('unread_count',0)->assertDontSee('Legacy secret');
        $this->post(route('notifications.open',$note->id))->assertNotFound();$this->post(route('notifications.open',$legacy->id))->assertNotFound();
        $this->get(route('customer-chat.show',$chat))->assertNotFound();
        $foreignStaff->revokePermissionTo('customer_chat.view');$foreignStaff->revokePermissionTo('customer_chat.reply');
        $this->actingAs($foreignStaff)->getJson(route('notifications.feed'))->assertJsonPath('unread_count',0)->assertJsonPath('chat_unread_count',0);
        $this->post(route('notifications.open',$note->id))->assertNotFound();$this->assertNull($note->fresh()->read_at);
    }
    public function test_staff_reply_notifies_only_own_customer_and_updates_floating_unread():void {
        $business=$this->store();$staff=$this->staff($business,'Kasir',['customer_chat.reply']);$account=$this->account();$other=$this->account('Other');
        $chat=$this->conversation($business,$account);$otherChat=$this->conversation($business,$other);
        $this->actingAs($staff)->postJson(route('customer-chat.send',$chat),['message'=>'Jam 7 tersedia','customer_account_id'=>$other->id])->assertOk();
        $note=$account->unreadNotifications()->sole();$this->assertEquals('Jam 7 tersedia',$note->data['message_preview']);$this->assertEquals(0,$other->notifications()->count());
        $this->actingAs($account,'customer')->get(route('customer.dashboard'))->assertOk()->assertSee('chat-unread')->assertSee('customer-floating-chat.js');
        $this->getJson(route('customer.chat.index'))->assertOk()->assertJsonPath('unread_count',1)->assertJsonPath('conversations.0.unread',1);
        $this->actingAs($other,'customer')->getJson(route('customer.chat.show',$chat))->assertNotFound();
        $this->getJson(route('customer.chat.index'))->assertJsonPath('unread_count',0);
        $this->actingAs($account,'customer')->getJson(route('customer.chat.show',$chat))->assertOk()->assertJsonPath('unread_count',0);
        $this->assertNotNull($note->fresh()->read_at);
    }
    public function test_customer_unread_total_includes_conversations_beyond_first_page():void {
        $account=$this->account();
        for($i=0;$i<21;$i++){
            $business=$this->store();$chat=Conversation::create(['business_id'=>$business->id,'customer_account_id'=>$account->id]);
            $chat->messages()->create(['sender_type'=>'staff','sender_id'=>1,'message'=>'Pesan '.$i]);
        }
        $this->actingAs($account,'customer')->getJson(route('customer.chat.index'))->assertOk()->assertJsonCount(20,'conversations')->assertJsonPath('unread_count',21);
    }
}
