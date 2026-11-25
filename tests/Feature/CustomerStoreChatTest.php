<?php
namespace Tests\Feature;

use App\Models\{Business,Category,Conversation,CustomerAccount,Product,User};
use App\Services\CustomerPortal;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http,Mail,Notification};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CustomerStoreChatTest extends TestCase
{
    use RefreshDatabase;
    private Business $store;
    private CustomerAccount $account;
    private Conversation $chat;
    protected function setUp():void
    {
        parent::setUp(); Http::preventStrayRequests(); Mail::fake(); Notification::fake();
        $this->store=$this->store('Toko Menu');
        $this->account=CustomerAccount::create(['name'=>'Amru','email'=>'amru@example.test','phone'=>'081234567890','password'=>'password']);
        app(CustomerPortal::class)->join($this->store,$this->account);
        $this->chat=Conversation::create(['business_id'=>$this->store->id,'customer_account_id'=>$this->account->id]);
        $this->actingAs($this->account,'customer');
    }
    private function store(string $name):Business
    {
        $guard=\Illuminate\Support\Facades\Auth::getDefaultDriver();
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        try {
            $store=Business::create(['name'=>$name,'slug'=>strtolower(str_replace(' ','-',$name)),'subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);
            $store->forceFill(['business_type'=>'retail'])->save(); return $store;
        } finally { \Illuminate\Support\Facades\Auth::shouldUse($guard); }
    }
    private function product(Business $store,string $name,bool $active=true):Product
    {
        app(TenantContext::class)->set($store);
        try {
            $category=Category::firstOrCreate(['name'=>'Menu'],['slug'=>'menu']);
            return Product::create(['name'=>$name,'category_id'=>$category->id,'price'=>25000,'stock'=>10,'is_active'=>$active]);
        } finally { app(TenantContext::class)->clear(); }
    }
    public function test_menu_contains_only_active_products_from_conversation_store_and_search_is_paginated():void
    {
        $this->product($this->store,'Nasi Goreng');
        $this->product($this->store,'Produk Disembunyikan',false);
        $other=$this->store('Toko Lain'); $this->product($other,'Rahasia Toko Lain');
        $this->get(route('customer.chat.show',$this->chat).'?business_id='.$other->id)->assertOk()->assertSee('Nasi Goreng')->assertDontSee('Produk Disembunyikan')->assertDontSee('Rahasia Toko Lain');
        for($i=0;$i<13;$i++) $this->product($this->store,'Minuman '.str_pad((string)$i,2,'0',STR_PAD_LEFT));
        $this->get(route('customer.chat.show',$this->chat).'?menu_search=Minuman')->assertOk()->assertViewHas('products',fn($products)=>$products->total()===13 && $products->count()===12)->assertDontSee('Nasi Goreng');
        $this->get(route('customer.chat.show',$this->chat).'?menu_search=Minuman&menu_page=2')->assertOk()->assertViewHas('products',fn($products)=>$products->count()===1);
    }
    public function test_product_question_uses_server_name_and_existing_store_notification():void
    {
        $product=$this->product($this->store,'Nasi Goreng');
        app(TenantContext::class)->set($this->store); $owner=User::factory()->create(); app(TenantContext::class)->clear();
        $owner->assignRole(Role::findOrCreate('Atasan','web'));
        $owner->givePermissionTo(Permission::findOrCreate('customer_chat.reply','web'));
        $this->postJson(route('customer.chat.send',$this->chat),['product_id'=>$product->id,'product_name'=>'Nama palsu','business_id'=>999,'message'=>'Apakah masih tersedia?'])->assertOk();
        $this->assertSame("Pertanyaan tentang produk: Nasi Goreng\nApakah masih tersedia?",$this->chat->messages()->sole()->message);
        Notification::assertSentTo($owner,\App\Notifications\PortalMessage::class);
        $this->assertSame($this->store->id,$this->chat->fresh()->business_id);
    }
    public function test_foreign_inactive_and_missing_products_are_rejected_without_saving_messages():void
    {
        $foreign=$this->product($this->store('Toko Lain'),'Produk Lain');
        $inactive=$this->product($this->store,'Produk Nonaktif',false);
        foreach([$foreign->id,$inactive->id,99999] as $id) $this->postJson(route('customer.chat.send',$this->chat),['product_id'=>$id,'message'=>'Ada?'])->assertUnprocessable()->assertJsonValidationErrors('product_id');
        $this->assertSame(0,$this->chat->messages()->count());
    }
    public function test_customer_cannot_view_or_send_to_another_customers_conversation():void
    {
        $other=CustomerAccount::create(['name'=>'Budi','email'=>'budi@example.test','phone'=>'081234567891','password'=>'password']);
        $this->actingAs($other,'customer')->get(route('customer.chat.show',$this->chat))->assertNotFound();
        $this->postJson(route('customer.chat.send',$this->chat),['message'=>'Pesan palsu'])->assertNotFound();
        $this->assertSame(0,$this->chat->messages()->count());
    }
    public function test_start_chat_reuses_conversation_and_general_questions_still_work():void
    {
        $this->post(route('customer.chat.start',$this->store->slug))->assertRedirect(route('customer.chat.show',$this->chat));
        $this->assertDatabaseCount('conversations',1);
        $this->get(route('customer.chat.show',$this->chat))->assertOk()->assertSee('Anda tetap dapat mengirim pertanyaan umum.');
        $this->postJson(route('customer.chat.send',$this->chat),['message'=>'Jam berapa toko buka?'])->assertOk();
        $this->assertSame('Jam berapa toko buka?',$this->chat->messages()->sole()->message);
    }
}
