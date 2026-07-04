<?php

namespace Tests\Feature\AdminApi\Product;

use App\Models\Admin;
use App\Models\Item;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** 成功硬刪除商品，其 product_rewards 明細一併被 cascade 清除 */
    public function testDeletesProductAndCascadesRewards(): void
    {
        // GIVEN 有 delete_products 權限的管理員，及一筆帶獎勵明細的商品
        $actor = $this->adminWith('delete_products');
        $product = Product::factory()->create();
        $item = Item::factory()->create();
        $reward = $product->productRewards()->create(['item_id' => $item->id, 'quantity' => 1]);

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/products/{$product->id}");

        // THEN  回傳 200，商品與其獎勵明細皆已從資料庫移除
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_rewards', ['id' => $reward->id]);
    }

    /** 刪除後，列表與詳情查詢皆不再回傳該商品 */
    public function testDeletedProductNoLongerAppearsInListOrShow(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('delete_products', 'view_products');
        $product = Product::factory()->create();

        // WHEN  刪除該商品
        $this->actingAs($actor, 'admin')->deleteJson("/admin-api/products/{$product->id}");

        // THEN  列表不再含該商品，詳情查詢回傳 404
        $listResponse = $this->actingAs($actor, 'admin')->getJson('/admin-api/products');
        $ids = collect($listResponse->json('data.items'))->pluck('id');
        $this->assertNotContains($product->id, $ids);

        $showResponse = $this->actingAs($actor, 'admin')->getJson("/admin-api/products/{$product->id}");
        $showResponse->assertNotFound();
    }

    /** 查無商品時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('delete_products');

        // WHEN  刪除不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson('/admin-api/products/999999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 delete_products 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆商品
        $actor = $this->adminWithNoPermission();
        $product = Product::factory()->create();

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/products/{$product->id}");

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
