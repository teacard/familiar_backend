<?php

namespace Tests\Feature\AdminApi\ProductType;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得商品類別分頁列表，items 每筆含 id、name、isDeletable */
    public function testReturnsProductTypeListWithIsDeletable(): void
    {
        // GIVEN 有 view_product_types 權限的管理員，及一筆商品類別
        $actor = $this->adminWith('view_product_types');
        ProductType::factory()->create(['name' => '道具']);

        // WHEN  發送 GET /admin-api/product-types
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types');

        // THEN  回傳 200，items 每筆含 id、name、isDeletable
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'name', 'isDeletable'],
                    ],
                    'currentPage',
                    'perPage',
                    'lastPage',
                ],
            ]);

        foreach ($response->json('data.items') as $entry) {
            $this->assertIsBool($entry['isDeletable']);
        }
    }

    /** 類別仍有商品使用時，isDeletable 為 false */
    public function testIsDeletableFalseWhenTypeInUse(): void
    {
        // GIVEN 有權限的管理員，及一個被商品使用的類別
        $actor = $this->adminWith('view_product_types');
        $usedType = ProductType::factory()->create(['name' => '使用中']);
        Product::factory()->create(['product_type_id' => $usedType->id]);

        // WHEN  發送 GET /admin-api/product-types
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types');

        // THEN  該類別的 isDeletable 為 false
        $response->assertOk();
        $entry = collect($response->json('data.items'))->firstWhere('name', '使用中');

        $this->assertNotNull($entry);
        $this->assertFalse($entry['isDeletable']);
    }

    /** 類別沒有任何商品使用時，isDeletable 為 true */
    public function testIsDeletableTrueWhenTypeUnused(): void
    {
        // GIVEN 有權限的管理員，及一個無人使用的類別
        $actor = $this->adminWith('view_product_types');
        ProductType::factory()->create(['name' => '未使用']);

        // WHEN  發送 GET /admin-api/product-types
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types');

        // THEN  該類別的 isDeletable 為 true
        $response->assertOk();
        $entry = collect($response->json('data.items'))->firstWhere('name', '未使用');

        $this->assertNotNull($entry);
        $this->assertTrue($entry['isDeletable']);
    }

    /** keyword 模糊搜尋，只回傳 name 含關鍵字的類別 */
    public function testFiltersByKeyword(): void
    {
        // GIVEN 有權限的管理員，及兩個 name 不同的類別
        $actor = $this->adminWith('view_product_types');
        ProductType::factory()->create(['name' => '道具類']);
        ProductType::factory()->create(['name' => '點數類']);

        // WHEN  發送 GET /admin-api/product-types?keyword=道具
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types?keyword=道具');

        // THEN  只回傳 name 含「道具」的類別
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('道具類', $names);
        $this->assertNotContains('點數類', $names);
    }

    /** perPage=25 時每頁最多 25 筆，且 perPage 回傳 25 */
    public function testRespectsPerPage(): void
    {
        // GIVEN 有權限的管理員，及 30 個類別
        $actor = $this->adminWith('view_product_types');
        ProductType::factory()->count(30)->create();

        // WHEN  帶 perPage=25
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types?perPage=25');

        // THEN  perPage 為 25，且 items 最多 25 筆
        $response->assertOk()
            ->assertJsonPath('data.perPage', 25);
        $this->assertCount(25, $response->json('data.items'));
    }

    /** perPage 傳入不合法值時回傳 422，errors.perPage 含錯誤訊息 */
    public function testRejectsInvalidPerPage(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('view_product_types');

        // WHEN  傳入 perPage=100
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types?perPage=100');

        // THEN  回傳 422，errors.perPage 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['perPage']);
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/product-types
        $response = $this->getJson('/admin-api/product-types');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 view_product_types 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送 GET /admin-api/product-types
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types');

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
