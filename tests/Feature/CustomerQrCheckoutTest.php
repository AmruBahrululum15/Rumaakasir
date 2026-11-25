<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Table;
use App\Services\MidtransService;
use Tests\Concerns\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CustomerQrCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_checkout_creates_customer_using_existing_customer_columns(): void
    {
        [$table, $product] = $this->tableAndProduct();

        $this->post(route('customer.checkout.store', $table), $this->checkoutData($product))
            ->assertRedirect(route('customer.checkout.success', ['table' => $table, 'sale' => Sale::firstOrFail()]));

        $customer = Customer::firstOrFail();
        $sale = Sale::firstOrFail();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Ayu Pelanggan',
            'phone' => '08123456789',
            'address' => 'ayu@example.test',
        ]);
        $this->assertNotNull($sale->id);
        $this->assertSame('table_qr', $sale->order_source);
        $this->assertSame($table->id, $sale->table_id);
        $this->assertSame($customer->id, $sale->customer_id);
    }

    public function test_qr_checkout_updates_existing_customer_without_duplicate(): void
    {
        [$table, $product] = $this->tableAndProduct();
        $customer = Customer::create([
            'name' => 'Nama Lama',
            'phone' => '08123456789',
            'address' => 'ayu@example.test',
        ]);

        $this->post(route('customer.checkout.store', $table), $this->checkoutData($product, ['customer_name' => 'Nama Baru']))
            ->assertRedirect();

        $this->assertSame(1, Customer::count());
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Nama Baru']);
        $this->assertSame('ayu@example.test', $customer->fresh()->address);
    }

    public function test_qr_checkout_requires_name_valid_email_and_phone(): void
    {
        [$table, $product] = $this->tableAndProduct();

        $this->from(route('customer.checkout', $table))
            ->post(route('customer.checkout.store', $table), array_merge($this->checkoutData($product), [
                'customer_name' => '',
                'customer_email' => 'invalid-email',
                'customer_phone' => '',
            ]))
            ->assertRedirect(route('customer.checkout', $table))
            ->assertSessionHasErrors(['customer_name', 'customer_email', 'customer_phone']);
    }

    public function test_qris_checkout_stores_customer_before_payment_callback(): void
    {
        [$table, $product] = $this->tableAndProduct();

        $midtrans = Mockery::mock(MidtransService::class);
        $midtrans->shouldReceive('createSnapToken')->once()->andReturn('snap-token');
        $this->app->instance(MidtransService::class, $midtrans);

        $this->post(route('customer.checkout.store', $table), $this->checkoutData($product, [
            'payment_method' => 'qris',
        ]))
            ->assertOk()
            ->assertViewIs('customer.checkout-payment')
            ->assertViewHas('snapToken', 'snap-token');

        $sale = Sale::firstOrFail();
        $customer = Customer::firstOrFail();

        $this->assertNotNull($sale->id);
        $this->assertDatabaseCount('customers', 1);
    }

    private function tableAndProduct(): array
    {
        $category = Category::create(['name' => 'Makanan', 'slug' => 'makanan', 'status' => true]);
        $product = Product::create([
            'name' => 'Nasi Goreng',
            'category_id' => $category->id,
            'price' => 15000,
            'stock' => 10,
            'status' => 'active',
            'is_active' => true,
        ]);
        $table = Table::create(['name' => 'Meja 1', 'qr_token' => 'meja-satu', 'is_active' => true]);

        return [$table, $product];
    }

    private function checkoutData(Product $product, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Ayu Pelanggan',
            'customer_email' => 'ayu@example.test',
            'customer_phone' => '08123456789',
            'payment_method' => 'cash',
            'cart' => json_encode([['id' => $product->id, 'quantity' => 1]]),
        ], $overrides);
    }
}
