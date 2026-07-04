<?php

namespace Tests\Feature\AdminApi\ProductType;

use App\Models\Admin;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** 成功更新 name，code 維持不變 */
    public function testUpdatesName(): void
    {
        // GIVEN 有 edit_product_types 權限的管理員，及一筆商品類別
        $actor = $this->adminWith('edit_product_types');
        $type = ProductType::factory()->create(['name' => '舊名稱']);
        $originalCode = $type->code;

        // WHEN  發送更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/product-types/{$type->id}", ['name' => '新名稱']);

        // THEN  回傳 200，name 已更新，code 不變
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseHas('product_types', [
            'id' => $type->id,
            'name' => '新名稱',
            'code' => $originalCode,
        ]);
    }

    /** name 超過 10 字元時回傳 422 */
    public function testRejectsNameExceeding10Chars(): void
    {
        // GIVEN 有權限的管理員，及一筆商品類別
        $actor = $this->adminWith('edit_product_types');
        $type = ProductType::factory()->create();

        // WHEN  name 為 11 字元
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/product-types/{$type->id}", ['name' => str_repeat('道', 11)]);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** 查無類別時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('edit_product_types');

        // WHEN  更新不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson('/admin-api/product-types/999999', ['name' => '新名稱']);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token，及一筆商品類別
        $type = ProductType::factory()->create();

        // WHEN  發送 PUT /admin-api/product-types/{id}
        $response = $this->putJson("/admin-api/product-types/{$type->id}", ['name' => '新名稱']);

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 edit_product_types 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆商品類別
        $actor = $this->adminWithNoPermission();
        $type = ProductType::factory()->create();

        // WHEN  發送更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/product-types/{$type->id}", ['name' => '新名稱']);

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
