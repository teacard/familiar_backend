<?php

namespace Tests\Feature\AdminApi\Product;

use App\Enums\Product\Status;
use App\Models\Admin;
use App\Models\Item;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得商品分頁列表，items 每筆含 id、name、productTypeName、amount、status、createdAt */
    public function testReturnsProductListStructure(): void
    {
        // GIVEN 有 view_products 權限的管理員，及一筆商品
        $actor = $this->adminWith('view_products');
        Product::factory()->create();

        // WHEN  發送 GET /admin-api/products
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products');

        // THEN  回傳 200，items 每筆含指定欄位
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'name', 'productTypeName', 'amount', 'status', 'createdAt'],
                    ],
                    'currentPage',
                    'perPage',
                    'lastPage',
                ],
            ]);
    }

    /** keyword 模糊搜尋，只回傳 name 含關鍵字的商品 */
    public function testFiltersByKeyword(): void
    {
        // GIVEN 有權限的管理員，及兩筆 name 不同的商品
        $actor = $this->adminWith('view_products');
        Product::factory()->create(['name' => '新手禮包']);
        Product::factory()->create(['name' => '進階禮包']);

        // WHEN  發送 GET /admin-api/products?keyword=新手
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products?keyword=' . urlencode('新手'));

        // THEN  只回傳 name 含「新手」的商品
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('新手禮包', $names);
        $this->assertNotContains('進階禮包', $names);
    }

    /** productTypeId 篩選，只回傳該類別的商品 */
    public function testFiltersByProductTypeId(): void
    {
        // GIVEN 有權限的管理員，及兩個不同類別各一筆商品
        $actor = $this->adminWith('view_products');
        $typeA = ProductType::factory()->create();
        $typeB = ProductType::factory()->create();
        $productA = Product::factory()->create(['product_type_id' => $typeA->id, 'name' => '商品A']);
        Product::factory()->create(['product_type_id' => $typeB->id, 'name' => '商品B']);

        // WHEN  發送 GET /admin-api/products?productTypeId={typeA}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/products?productTypeId={$typeA->id}");

        // THEN  只回傳該類別的商品
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains($productA->name, $names);
        $this->assertNotContains('商品B', $names);
    }

    /** itemId 篩選，只回傳獎勵內容包含該道具的商品 */
    public function testFiltersByItemId(): void
    {
        // GIVEN 有權限的管理員，及各自獎勵內容含不同道具的兩筆商品
        $actor = $this->adminWith('view_products');
        $itemA = Item::factory()->create();
        $itemB = Item::factory()->create();
        $productA = Product::factory()->create(['name' => '商品A']);
        $productA->productRewards()->create(['item_id' => $itemA->id, 'quantity' => 1]);
        $productB = Product::factory()->create(['name' => '商品B']);
        $productB->productRewards()->create(['item_id' => $itemB->id, 'quantity' => 1]);

        // WHEN  發送 GET /admin-api/products?itemId={itemA}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/products?itemId={$itemA->id}");

        // THEN  只回傳獎勵內容含 itemA 的商品
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('商品A', $names);
        $this->assertNotContains('商品B', $names);
    }

    /** status 篩選，只回傳符合狀態的商品 */
    public function testFiltersByStatus(): void
    {
        // GIVEN 有權限的管理員，及一筆上架、一筆下架商品
        $actor = $this->adminWith('view_products');
        Product::factory()->published()->create(['name' => '上架商品']);
        Product::factory()->unpublished()->create(['name' => '下架商品']);

        // WHEN  發送 GET /admin-api/products?status=published
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products?status=' . Status::PUBLISHED->value);

        // THEN  只回傳上架商品
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('上架商品', $names);
        $this->assertNotContains('下架商品', $names);
    }

    /** 未帶 status 篩選時，預設回傳上架與下架的所有商品 */
    public function testReturnsAllStatusesByDefault(): void
    {
        // GIVEN 有權限的管理員，及一筆上架、一筆下架商品
        $actor = $this->adminWith('view_products');
        Product::factory()->published()->create(['name' => '上架商品']);
        Product::factory()->unpublished()->create(['name' => '下架商品']);

        // WHEN  發送 GET /admin-api/products（不帶 status）
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products');

        // THEN  兩筆商品都出現
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('上架商品', $names);
        $this->assertContains('下架商品', $names);
    }

    /** perPage=25 時每頁最多 25 筆，且 perPage 回傳 25 */
    public function testRespectsPerPage(): void
    {
        // GIVEN 有權限的管理員，及 30 筆商品
        $actor = $this->adminWith('view_products');
        Product::factory()->count(30)->create();

        // WHEN  帶 perPage=25
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products?perPage=25');

        // THEN  perPage 為 25，且 items 最多 25 筆
        $response->assertOk()
            ->assertJsonPath('data.perPage', 25);
        $this->assertCount(25, $response->json('data.items'));
    }

    /** perPage 傳入不合法值時回傳 422 */
    public function testRejectsInvalidPerPage(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('view_products');

        // WHEN  傳入 perPage=100
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products?perPage=100');

        // THEN  回傳 422，errors.perPage 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['perPage']);
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/products
        $response = $this->getJson('/admin-api/products');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 view_products 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送 GET /admin-api/products
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/products');

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
