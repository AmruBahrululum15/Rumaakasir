<?php

namespace Tests\Feature;

use App\Models\{Business, Category, Product, Table, User};
use App\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\{DB, Http, Mail, Notification};
use Spatie\Permission\Models\{Permission, Role};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class CategoryBusinessIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests(); Mail::fake(); Notification::fake();
    }

    private function tenant(string $name, string $categoryName='Makanan'): array
    {
        $business=Business::create(['name'=>$name,'subscription_plan'=>'premium','subscription_ends_at'=>now()->addMonth()]);
        $business->forceFill(['business_type'=>'cafe'])->save();
        app(TenantContext::class)->set($business);
        $user=User::factory()->create();
        foreach (['categories.view','categories.create','categories.edit','categories.delete','products.view','products.create','products.edit'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission,'web'));
        }
        $category=Category::create(['name'=>$categoryName,'slug'=>\Illuminate\Support\Str::slug($categoryName),'status'=>true]);
        $product=Product::create(['name'=>'Menu '.$name,'category_id'=>$category->id,'price'=>15000,'stock'=>10,'status'=>'active','is_active'=>true]);
        $table=Table::create(['name'=>'Meja '.$name,'qr_token'=>uniqid('qr-'),'is_active'=>true]);
        app(TenantContext::class)->clear();
        return [$business,$user,$category,$product,$table];
    }

    private function categoryData(array $overrides=[]): array
    {
        return array_replace(['name'=>'Minuman','status'=>true],$overrides);
    }

    public function test_staff_can_create_same_name_in_two_stores_but_not_twice_in_one_store(): void
    {
        [$a,$staffA]=$this->tenant('A','Kategori Awal');[$b,$staffB]=$this->tenant('B','Kategori Awal');
        foreach ([[$a,$staffA,$b],[$b,$staffB,$a]] as [$store,$staff,$foreign]) {
            $this->actingAs($staff)->post(route('categories.store'),$this->categoryData(['name'=>'Makanan','business_id'=>$foreign->id]))->assertRedirect(route('categories.index'))->assertSessionHasNoErrors();
            $this->assertDatabaseHas('categories',['business_id'=>$store->id,'name'=>'Makanan','slug'=>'makanan']);
            $this->post(route('categories.store'),$this->categoryData(['name'=>'Makanan']))->assertSessionHasErrors('name');
        }
        $this->assertEquals(2,DB::table('categories')->where('name','Makanan')->count());
    }

    public function test_update_ignores_current_category_but_rejects_same_store_duplicate(): void
    {
        [$a,$staff,$category]=$this->tenant('A');[$b,,$foreign]=$this->tenant('B');
        $this->actingAs($staff)->put(route('categories.update',$category),$this->categoryData(['name'=>'Makanan','business_id'=>$b->id]))->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('categories.store'),$this->categoryData())->assertSessionHasNoErrors();
        $this->put(route('categories.update',$category),$this->categoryData())->assertSessionHasErrors('name');
        $this->assertDatabaseHas('categories',['id'=>$category->id,'business_id'=>$a->id,'name'=>'Makanan']);
        $this->assertDatabaseHas('categories',['id'=>$foreign->id,'business_id'=>$b->id,'name'=>'Makanan']);
    }

    public function test_list_detail_edit_and_delete_are_scoped_to_the_authenticated_store(): void
    {
        [$a,$staff,$own]=$this->tenant('A');[$b,,$foreign]=$this->tenant('B');
        $this->actingAs($staff)->get(route('categories.index'))->assertOk()->assertViewHas('categories',fn($rows)=>$rows->pluck('id')->all()===[$own->id]);
        foreach (['categories.show','categories.edit'] as $route) $this->get(route($route,$foreign))->assertNotFound();
        $this->put(route('categories.update',$foreign),$this->categoryData())->assertNotFound();
        $this->delete(route('categories.destroy',$foreign))->assertNotFound();
        $this->assertDatabaseHas('categories',['id'=>$foreign->id,'name'=>'Makanan','deleted_at'=>null]);
    }

    public function test_product_dropdown_and_create_update_reject_foreign_categories(): void
    {
        Storage::fake('public');
        [$a,$staff,$own,$product]=$this->tenant('A');[$b,,$foreign]=$this->tenant('B');
        $this->actingAs($staff);
        foreach ([route('products.create'),route('products.edit',$product)] as $url) {
            $this->get($url)->assertOk()->assertViewHas('categories',fn($rows)=>$rows->pluck('id')->all()===[$own->id]);
        }
        $data=['name'=>'Produk Baru','price'=>10000,'stock'=>3,'status'=>'active','category_id'=>$foreign->id,'business_id'=>$b->id];
        $this->post(route('products.store'),$data)->assertSessionHasErrors('category_id');
        $this->put(route('products.update',$product),$data)->assertSessionHasErrors('category_id');
        $data['category_id']=$own->id;
        $data['image']=UploadedFile::fake()->image('produk.png');
        $this->post(route('products.store'),$data)->assertRedirect(route('products.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products',['business_id'=>$a->id,'name'=>'Produk Baru','category_id'=>$own->id]);
        $this->assertDatabaseHas('products',['id'=>$product->id,'category_id'=>$own->id]);
    }

    public function test_customer_portal_uses_opened_store_even_with_foreign_business_and_category_input(): void
    {
        [$a,,$categoryA,$productA]=$this->tenant('A');[$b,,$categoryB,$productB]=$this->tenant('B');
        foreach ([[$a,$categoryA,$productA,$b,$productB],[$b,$categoryB,$productB,$a,$productA]] as [$store,$category,$product,$foreign,$foreignProduct]) {
            $this->get(route('customer.store',['business'=>$store->slug,'business_id'=>$foreign->id]))->assertOk()
                ->assertViewHas('categories',fn($rows)=>$rows->pluck('id')->all()===[$category->id])
                ->assertSee($product->name)->assertDontSee($foreignProduct->name);
        }
        $this->get(route('customer.store',['business'=>$a->slug,'category'=>$categoryB->id]))->assertOk()->assertViewHas('products',fn($rows)=>$rows->isEmpty());
    }

    public function test_qr_token_controls_store_and_foreign_cart_product_is_rejected(): void
    {
        [$a,,$categoryA,$productA,$tableA]=$this->tenant('A');[$b,,$categoryB,$productB,$tableB]=$this->tenant('B');
        foreach ([[$tableA,$categoryA,$productA,$b,$productB],[$tableB,$categoryB,$productB,$a,$productA]] as [$table,$category,$product,$foreign,$foreignProduct]) {
            $this->get(route('customer.menu',['qr_token'=>$table->qr_token,'business_id'=>$foreign->id]))->assertOk()
                ->assertViewHas('products',fn($rows)=>$rows->pluck('id')->all()===[$product->id] && $rows->first()->category->id===$category->id)
                ->assertSee($product->name)->assertDontSee($foreignProduct->name);
        }
        $this->post(route('customer.checkout.store',$tableA),['business_id'=>$b->id,'customer_name'=>'Customer','customer_email'=>'test@example.test','customer_phone'=>'08123456789','payment_method'=>'cash','cart'=>json_encode([['id'=>$productB->id,'quantity'=>1]])])->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseCount('sales',0);
    }

    public function test_database_constraint_rejects_duplicate_names_even_with_different_slugs(): void
    {
        [$business]=$this->tenant('A');
        $this->expectException(QueryException::class);
        DB::table('categories')->insert(['business_id'=>$business->id,'name'=>'Makanan','slug'=>'different-slug','status'=>true]);
    }

    public function test_deleted_category_names_remain_reserved_and_slug_collisions_are_handled(): void
    {
        [$business,$staff]=$this->tenant('A');
        $this->actingAs($staff)->post(route('categories.store'),$this->categoryData(['name'=>'Minuman!']))->assertSessionHasNoErrors();
        $deleted=Category::withoutGlobalScope('business')->where('business_id',$business->id)->where('name','Minuman!')->firstOrFail();
        $this->delete(route('categories.destroy',$deleted))->assertRedirect();
        $this->post(route('categories.store'),$this->categoryData(['name'=>'Minuman!']))->assertSessionHasErrors('name');
        $this->post(route('categories.store'),$this->categoryData())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories',['business_id'=>$business->id,'name'=>'Minuman','slug'=>'minuman-1']);
        $this->post(route('products.store'),['name'=>'Test','price'=>1000,'stock'=>1,'status'=>'active','category_id'=>$deleted->id])->assertSessionHasErrors('category_id');
    }

    public function test_super_admin_uses_explicit_selected_tenant_for_category_validation(): void
    {
        [$a,$admin]=$this->tenant('A');[$b]=$this->tenant('B');
        $admin->assignRole(Role::findOrCreate('Super Admin','web'));
        $this->actingAs($admin)->withSession(['super_admin_business_id'=>$b->id])->post(route('categories.store'),$this->categoryData(['business_id'=>$a->id]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('categories',['business_id'=>$b->id,'name'=>'Minuman']);
        $this->assertDatabaseMissing('categories',['business_id'=>$a->id,'name'=>'Minuman']);
    }
}
