<?php

namespace Tests\Feature\AdminApi\Product;

use App\Enums\Media\CollectionName;
use App\Models\Admin;
use App\Models\Item;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 可取得商品詳情，含 productType、image、productRewards */
    public function testReturnsProductDetail(): void
    {
        // GIVEN 有 view_products 權限的管理員，及一筆含主圖、獎勵明細的商品
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWith('view_products');
        $productType = ProductType::factory()->create(['name' => '道具']);
        $product = Product::factory()->create(['name' => '新手禮包', 'product_type_id' => $productType->id]);
        $product->addMedia(UploadedFile::fake()->image('product.png'))
            ->toMediaCollection(CollectionName::PRODUCT->value);
        $item = Item::factory()->create(['name' => '金幣']);
        $product->productRewards()->create(['item_id' => $item->id, 'quantity' => 100]);

        // WHEN  發送 GET /admin-api/products/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/products/{$product->id}");

        // THEN  回傳 200，含完整詳情欄位
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'name', 'amount', 'status',
                    'productType' => ['id', 'name'],
                    'image' => ['id', 'url'],
                    'productRewards' => ['*' => ['item' => ['id', 'name'], 'quantity']],
                ],
            ])
            ->assertJsonPath('data.name', '新手禮包')
            ->assertJsonPath('data.productType.id', $productType->id)
            ->assertJsonPath('data.productType.name', '道具')
            ->assertJsonPath('data.productRewards.0.item.id', $item->id)
            ->assertJsonPath('data.productRewards.0.item.name', '金幣')
            ->assertJsonPath('data.productRewards.0.quantity', 100);
    }

    /** 查無商品時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('view_products');

        // WHEN  查詢不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products/999999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 view_products 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆含主圖的商品
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWithNoPermission();
        $product = Product::factory()->create();
        $product->addMedia(UploadedFile::fake()->image('product.png'))
            ->toMediaCollection(CollectionName::PRODUCT->value);

        // WHEN  發送 GET /admin-api/products/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/products/{$product->id}");

        // THEN  回傳 403
        $response->assertForbidden();
    }

    private function adminWith(string ...$permissions): Admin
    {
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'admin']);
        }
        $role = Role::create(['name' => 'actor_role', 'guard_name' => 'admin']);
        $role->givePermissionTo($permissions);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }

    private function adminWithNoPermission(): Admin
    {
        $role = Role::create(['name' => 'empty_role', 'guard_name' => 'admin']);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }
}
