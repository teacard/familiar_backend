<?php

namespace Tests\Feature\AdminApi\Item;

use App\Enums\Item\Status;
use App\Models\Admin;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SelectTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得下拉清單，每筆含 label（對應 name）與 value（對應 id） */
    public function testReturnsSelectOptionsWithLabelAndValue(): void
    {
        // GIVEN 已登入後台人員，及一筆道具
        $actor = $this->admin();
        $item = Item::factory()->create(['name' => '金幣']);

        // WHEN  發送 GET /admin-api/items/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items/select');

        // THEN  回傳 200，data 為陣列，含該道具的 label=name、value=id
        $response->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['label', 'value']]]);

        $entry = collect($response->json('data'))->firstWhere('value', $item->id);
        $this->assertNotNull($entry);
        $this->assertSame('金幣', $entry['label']);
    }

    /** 不分頁：data 為純陣列，回傳全部道具 */
    public function testReturnsAllItemsWithoutPagination(): void
    {
        // GIVEN 已登入後台人員，及多於單頁預設筆數的道具
        $actor = $this->admin();
        Item::factory()->count(30)->create();

        // WHEN  發送 GET /admin-api/items/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items/select');

        // THEN  data 為純陣列（無分頁欄位），且包含全部 30 筆
        $response->assertOk();
        $data = $response->json('data');
        $this->assertTrue(array_is_list($data));
        $this->assertCount(30, $data);
    }

    /** isActive=true 時，停用中的道具不會出現在下拉選單（比照角色下拉選單排除 is_system 角色的邏輯，供建立/編輯商品獎勵時使用） */
    public function testExcludesDisabledItemsWhenIsActiveTrue(): void
    {
        // GIVEN 已登入後台人員，及一筆啟用、一筆停用的道具
        $actor = $this->admin();
        $active = Item::factory()->create(['name' => '啟用中', 'status' => Status::ACTIVE]);
        $disabled = Item::factory()->create(['name' => '已停用', 'status' => Status::DISABLED]);

        // WHEN  發送 GET /admin-api/items/select?isActive=true
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items/select?isActive=true');

        // THEN  只回傳啟用中的道具，停用中的道具不出現
        $response->assertOk();
        $values = collect($response->json('data'))->pluck('value');
        $this->assertContains($active->id, $values);
        $this->assertNotContains($disabled->id, $values);
    }

    /** 未帶 isActive（或帶 false）時，停用中的道具仍會出現在下拉選單（供商品依道具篩選時，仍可搜尋到已停用道具的既有商品） */
    public function testIncludesDisabledItemsWhenIsActiveOmitted(): void
    {
        // GIVEN 已登入後台人員，及一筆啟用、一筆停用的道具
        $actor = $this->admin();
        $active = Item::factory()->create(['name' => '啟用中', 'status' => Status::ACTIVE]);
        $disabled = Item::factory()->create(['name' => '已停用', 'status' => Status::DISABLED]);

        // WHEN  發送 GET /admin-api/items/select（不帶 isActive）
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items/select');

        // THEN  啟用中與停用中的道具皆會出現
        $response->assertOk();
        $values = collect($response->json('data'))->pluck('value');
        $this->assertContains($active->id, $values);
        $this->assertContains($disabled->id, $values);
    }

    /** 不具 view_items 權限仍可取得（不額外檢查權限） */
    public function testReturnsOkWithoutPermission(): void
    {
        // GIVEN 已登入但無任何權限的後台人員
        $actor = $this->admin();

        // WHEN  發送 GET /admin-api/items/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items/select');

        // THEN  回傳 200（不因缺權限而 403）
        $response->assertOk();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/items/select
        $response = $this->getJson('/admin-api/items/select');

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
