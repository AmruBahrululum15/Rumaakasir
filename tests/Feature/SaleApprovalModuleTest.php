<?php
namespace Tests\Feature;
use App\Models\{Business,Category,Product,Sale,SaleApproval,SaleDetail,User};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Http,Mail,Notification};
use Spatie\Permission\Models\{Permission,Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;
class SaleApprovalModuleTest extends TestCase {
    use RefreshDatabase;
    private User $owner;private User $cashier;private Sale $sale;private Product $product;
    protected function setUp(): void {
        parent::setUp();Http::preventStrayRequests();Mail::fake();Notification::fake();
        config(['premium_features.features.approval_transactions.implemented'=>true,'premium_features.features.approval_transactions.status'=>'available']);
        $b=Business::create(['name'=>'Refund','subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);$b->forceFill(['business_type'=>'retail'])->save();app(TenantContext::class)->set($b);
        $this->owner=User::factory()->create();$this->owner->assignRole(Role::findOrCreate('Atasan','web'));$this->cashier=User::factory()->create();$this->cashier->assignRole(Role::findOrCreate('Kasir','web'));
        foreach([$this->owner,$this->cashier] as $u)$u->givePermissionTo(Permission::findOrCreate('sales.create','web'));
        $this->owner->givePermissionTo(Permission::findOrCreate('sales.approve-refunds','web'));
        $c=Category::create(['name'=>'Produk','slug'=>'produk']);$this->product=Product::create(['name'=>'Produk','category_id'=>$c->id,'price'=>10000,'stock'=>8]);
        $this->sale=Sale::create(['code'=>'REF-1','sale_date'=>now(),'total'=>20000,'paid'=>20000,'change'=>0,'payment_status'=>'PAID','payment_method'=>'cash']);
        SaleDetail::create(['sale_id'=>$this->sale->id,'product_id'=>$this->product->id,'quantity'=>2,'price'=>10000,'subtotal'=>20000]);app(TenantContext::class)->clear();
    }
    private function requestRefund(string $type='refund') {
        $this->actingAs($this->cashier)->post(route('sale-approvals.store'),['sale_id'=>$this->sale->id,'type'=>$type,'reason'=>'Customer meminta pembatalan'])->assertRedirect();return SaleApproval::firstOrFail();
    }
    public function test_cashier_requests_owner_approves_once_and_stock_is_restored_without_deleting_sale(): void {
        $a=$this->requestRefund();$this->get(route('sale-approvals.index'))->assertOk();
        Notification::assertSentTo($this->owner,\App\Notifications\SaleApprovalRequested::class);
        $this->patch(route('sale-approvals.review',$a),['decision'=>'approve','review_note'=>'Sudah diperiksa','cash_returned'=>1])->assertForbidden();
        $payload=['decision'=>'approve','review_note'=>'Uang tunai dikembalikan','cash_returned'=>1];
        $this->actingAs($this->owner)->patch(route('sale-approvals.review',$a),$payload)->assertRedirect();
        $this->assertSame('REFUNDED',$this->sale->fresh()->payment_status);$this->assertSame(10,$this->product->fresh()->stock);$this->assertDatabaseCount('sales',1);$this->assertDatabaseCount('business_audit_entries',2);
        $this->patch(route('sale-approvals.review',$a),$payload)->assertStatus(409);$this->assertSame(10,$this->product->fresh()->stock);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('totalRevenue',0);
        $this->assertDatabaseHas('sales',['id'=>$this->sale->id,'payment_status'=>'REFUNDED']);
    }
    public function test_rejection_does_not_change_sale(): void {
        $a=$this->requestRefund();$this->actingAs($this->owner)->patch(route('sale-approvals.review',$a),['decision'=>'reject','review_note'=>'Alasan belum sesuai'])->assertRedirect();
        $this->assertSame('rejected',$a->fresh()->status);$this->assertSame('PAID',$this->sale->fresh()->payment_status);$this->assertSame(8,$this->product->fresh()->stock);
    }
    public function test_void_only_for_unpaid_cash_and_online_refund_is_not_faked(): void {
        $this->sale->update(['payment_status'=>'CASH_PENDING']);$a=$this->requestRefund('void');
        $this->actingAs($this->owner)->patch(route('sale-approvals.review',$a),['decision'=>'approve','review_note'=>'Belum menerima pembayaran','cash_returned'=>1])->assertRedirect();$this->assertSame('VOIDED',$this->sale->fresh()->payment_status);
        $this->sale->update(['payment_status'=>'PAID','payment_method'=>'qris']);
        $this->post(route('sale-approvals.store'),['sale_id'=>$this->sale->id,'type'=>'refund','reason'=>'Permintaan refund'])->assertSessionHasErrors('sale_id');
    }
    public function test_cross_tenant_free_duplicate_and_missing_reason_are_blocked(): void {
        $this->actingAs($this->cashier)->post(route('sale-approvals.store'),['sale_id'=>$this->sale->id,'type'=>'refund'])->assertSessionHasErrors('reason');
        $a=$this->requestRefund();$this->post(route('sale-approvals.store'),['sale_id'=>$this->sale->id,'type'=>'refund','reason'=>'Sekali lagi'])->assertStatus(409);
        $other=Business::create(['name'=>'Other','subscription_plan'=>'premium']);$other->forceFill(['business_type'=>'retail'])->save();app(TenantContext::class)->set($other);$u=User::factory()->create();$u->assignRole(Role::findOrCreate('Atasan','web'));$u->givePermissionTo(['sales.create','sales.approve-refunds']);app(TenantContext::class)->clear();
        $this->actingAs($u)->patch(route('sale-approvals.review',$a),[])->assertNotFound();
        $this->owner->business->update(['subscription_plan'=>'free']);$this->actingAs($this->owner)->getJson(route('sale-approvals.index'))->assertForbidden();
    }
}
