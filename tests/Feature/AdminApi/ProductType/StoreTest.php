<?php

namespace Tests\Feature\AdminApi\ProductType;

use App\Models\Admin;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** 合法資料可成功建立商品類別，並自動產生 8 碼 code */
    public function testCreatesProductType(): void
    {
        // GIVEN 有 create_product_types 權限的管理員
        $actor = $this->adminWith('create_product_types');

        // WHEN  送出合法建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/product-types', ['name' => '道具']);

        // THEN  回傳 200，類別已建立，code 為系統自動產生的 8 碼
        $response->assertOk()->assertJsonPath('data', []);
        $type = ProductType::where('name', '道具')->first();
        $this->assertNotNull($type);
        $this->assertSame(8, strlen($type->code));
    }

    /** 不同商品類別各自產生不重複的 code */
    public function testGeneratesUniqueCodePerType(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_product_types');

        // WHEN  連續建立兩筆類別
        $this->actingAs($actor, 'admin')->postJson('/admin-api/product-types', ['name' => '道具']);
        $this->actingAs($actor, 'admin')->postJson('/admin-api/product-types', ['name' => '點數']);

        // THEN  兩筆的 code 不相同
        $codes = ProductType::pluck('code');
        $this->assertSame($codes->count(), $codes->unique()->count());
    }

    /** name 超過 10 字元時回傳 422，errors.name 含錯誤訊息 */
    public function testRejectsNameExceeding10Chars(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_product_types');

        // WHEN  name 為 11 字元
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/product-types', ['name' => str_repeat('道', 11)]);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** 未提供 name 時回傳 422，errors.name 含錯誤訊息 */
    public function testRejectsMissingName(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_product_types');

        // WHEN  body 不含 name
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/product-types', []);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 POST /admin-api/product-types
        $response = $this->postJson('/admin-api/product-types', ['name' => '道具']);

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 create_product_types 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送 POST /admin-api/product-types
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/product-types', ['name' => '道具']);

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
