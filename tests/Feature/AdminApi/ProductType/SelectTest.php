<?php

namespace Tests\Feature\AdminApi\ProductType;

use App\Models\Admin;
use App\Models\ProductType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SelectTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得下拉清單，每筆含 label（對應 name）與 value（對應 id） */
    public function testReturnsSelectOptionsWithLabelAndValue(): void
    {
        // GIVEN 已登入後台人員，及一筆商品類別
        $actor = $this->admin();
        $type = ProductType::factory()->create(['name' => '道具']);

        // WHEN  發送 GET /admin-api/product-types/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types/select');

        // THEN  回傳 200，data 為陣列，含該類別的 label=name、value=id
        $response->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['label', 'value']]]);

        $entry = collect($response->json('data'))->firstWhere('value', $type->id);
        $this->assertNotNull($entry);
        $this->assertSame('道具', $entry['label']);
    }

    /** 不分頁：data 為純陣列，回傳全部類別 */
    public function testReturnsAllTypesWithoutPagination(): void
    {
        // GIVEN 已登入後台人員，及多於單頁預設筆數的類別
        $actor = $this->admin();
        ProductType::factory()->count(30)->create();

        // WHEN  發送 GET /admin-api/product-types/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types/select');

        // THEN  data 為純陣列（無分頁欄位），且包含全部 30 筆
        $response->assertOk();
        $data = $response->json('data');
        $this->assertTrue(array_is_list($data));
        $this->assertCount(30, $data);
    }

    /** 不具 view_product_types 權限仍可取得（不額外檢查權限） */
    public function testReturnsOkWithoutPermission(): void
    {
        // GIVEN 已登入但無任何權限的後台人員
        $actor = $this->admin();

        // WHEN  發送 GET /admin-api/product-types/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/product-types/select');

        // THEN  回傳 200（不因缺權限而 403）
        $response->assertOk();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/product-types/select
        $response = $this->getJson('/admin-api/product-types/select');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 建立一個具角色但無任何權限的已登入後台人員 */
    private function admin(): Admin
    {
        $role = Role::create(['name' => 'actor_role', 'guard_name' => 'admin']);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }
}
