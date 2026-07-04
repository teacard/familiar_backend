<?php

namespace Tests\Feature\AdminApi\ProductType;

use App\Enums\ApiCode;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** 未被使用的類別可成功刪除 */
    public function testDeletesUnusedType(): void
    {
        // GIVEN 有 delete_product_types 權限的管理員，及一個無人使用的類別
        $actor = $this->adminWith('delete_product_types');
        $type = ProductType::factory()->create();

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/product-types/{$type->id}");

        // THEN  回傳 200，類別已從資料庫移除
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseMissing('product_types', ['id' => $type->id]);
    }

    /** 類別仍被商品使用時，回傳 422 並帶 PRODUCT_TYPE_IN_USE，類別未被刪除 */
    public function testRejectsDeletingTypeInUse(): void
    {
        // GIVEN 有權限的管理員，及一個被商品使用的類別
        $actor = $this->adminWith('delete_product_types');
        $type = ProductType::factory()->create();
        Product::factory()->create(['product_type_id' => $type->id]);

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/product-types/{$type->id}");

        // THEN  回傳 422，apiCode 為 PRODUCT_TYPE_IN_USE，類別仍存在
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::PRODUCT_TYPE_IN_USE->value);
        $this->assertDatabaseHas('product_types', ['id' => $type->id]);
    }

    /** 查無類別時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('delete_product_types');

        // WHEN  刪除不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson('/admin-api/product-types/999999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token，及一筆商品類別
        $type = ProductType::factory()->create();

        // WHEN  發送 DELETE /admin-api/product-types/{id}
        $response = $this->deleteJson("/admin-api/product-types/{$type->id}");

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 delete_product_types 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆商品類別
        $actor = $this->adminWithNoPermission();
        $type = ProductType::factory()->create();

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/product-types/{$type->id}");

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
