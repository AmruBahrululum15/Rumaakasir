<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CashierTransactionContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_context_is_server_owned_and_customer_is_optional(): void
    {
        $cashier = User::factory()->create(['name' => 'Kasir A', 'is_active' => true]);
        $otherUser = User::factory()->create(['name' => 'Tidak Boleh Dipakai', 'is_active' => true]);
        $cashier->givePermissionTo(Permission::create(['name' => 'sales.create', 'guard_name' => 'web']));
        $category = Category::create(['name' => 'Makanan', 'slug' => 'makanan', 'status' => true]);
        $product = Product::create(['name' => 'Produk A', 'category_id' => $category->id, 'price' => 10000, 'stock' => 5, 'status' => 'active', 'is_active' => true]);

        $this->actingAs($cashier)->post(route('sales.store'), [
            'sale_date' => now()->toDateString(),
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'paid' => 10000,
            // Malicious request values must be ignored by the controller.
            'user_id' => $otherUser->id,
            'cashier_id' => $otherUser->id,
            'order_source' => 'table_qr',
            'products' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::with(['customer', 'user'])->firstOrFail();
        $this->assertSame($cashier->id, $sale->user_id);
        $this->assertSame('Budi', $sale->customer?->name);
        $this->assertSame('cash', $sale->payment_method);
        $this->assertSame('PAID', $sale->payment_status);
        $this->assertSame('cashier', $sale->order_source);
        $this->assertSame(4, $product->fresh()->stock);
    }
}
