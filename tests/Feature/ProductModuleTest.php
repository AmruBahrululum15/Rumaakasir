<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Tests\Concerns\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_edit_and_view_products_with_category_and_supplier(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $role = Role::create(['name' => 'Product Manager', 'guard_name' => 'web']);

        foreach (['products.view', 'products.create', 'products.edit'] as $name) {
            $role->givePermissionTo(Permission::create([
                'name' => $name,
                'guard_name' => 'web',
            ]));
        }

        $user->assignRole($role);

        $category = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
            'status' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'PT Pemasok Utama',
        ]);

        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertOk()
            ->assertViewHas('categories')
            ->assertViewHas('suppliers')
            ->assertSee($category->name)
            ->assertSee($supplier->name);

        $this->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'Es Teh',
                'category_id' => $category->id,
                'supplier_id' => $supplier->id,
                'price' => 5000,
                'stock' => 10,
                'status' => 'active',
                'image' => UploadedFile::fake()->image('es-teh.jpg'),
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'status' => 'active',
            'is_active' => 1,
        ]);
        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->supplier->is($supplier));
        Storage::disk('public')->assertExists($product->image);

        $this->actingAs($user)
            ->get(route('products.index', ['search' => $supplier->name]))
            ->assertOk()
            ->assertSee('Es Teh');

        $this->actingAs($user)
            ->put(route('products.update', $product), [
                'name' => 'Es Teh Manis',
                'category_id' => $category->id,
                'supplier_id' => '',
                'price' => 6000,
                'stock' => 8,
                'status' => 'inactive',
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Es Teh Manis',
            'supplier_id' => null,
            'status' => 'inactive',
            'is_active' => 0,
        ]);
    }
}
