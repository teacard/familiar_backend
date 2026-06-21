<?php

namespace Tests\Feature\AdminApi\Role;

use App\Enums\Auth\Guard;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SelectTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得角色下拉清單，每筆僅含 id、name，不含 isDeletable / permissions */
    public function testReturnsRoleSelectWithIdAndNameOnly(): void
    {
        // GIVEN 已登入後台人員，及一個 admin guard 角色
        $actor = $this->admin();
        Role::create(['name' => 'editor', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 GET /admin-api/roles/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles/select');

        // THEN  回傳 200，data 為陣列，每筆僅含 id、name
        $response->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]]);

        foreach ($response->json('data') as $entry) {
            $this->assertSame(['id', 'name'], array_keys($entry));
        }
    }

    /** 系統角色（is_system=true）不出現在下拉清單 */
    public function testExcludesSystemRole(): void
    {
        // GIVEN 已登入後台人員，及一個系統角色
        $actor = $this->admin();
        Role::create(['name' => 'system_role', 'guard_name' => Guard::ADMIN->value, 'is_system' => true]);

        // WHEN  發送 GET /admin-api/roles/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles/select');

        // THEN  system_role 不出現
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertNotContains('system_role', $names);
    }

    /** 只回傳 admin guard 的角色，web guard 角色不出現 */
    public function testOnlyReturnsAdminGuardRoles(): void
    {
        // GIVEN 已登入後台人員，同時存在一個 web guard 角色
        $actor = $this->admin();
        Role::create(['name' => 'web_role', 'guard_name' => Guard::WEB->value]);

        // WHEN  發送 GET /admin-api/roles/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles/select');

        // THEN  web_role 不出現
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertNotContains('web_role', $names);
    }

    /** 不分頁：data 為純陣列（無分頁欄位），且回傳全部符合條件的角色 */
    public function testReturnsAllRolesWithoutPagination(): void
    {
        // GIVEN 已登入後台人員，及多於單頁預設筆數的角色
        $actor = $this->admin();
        for ($i = 0; $i < 30; ++$i) {
            Role::create(['name' => "role_{$i}", 'guard_name' => Guard::ADMIN->value]);
        }

        // WHEN  發送 GET /admin-api/roles/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles/select');

        // THEN  data 為純陣列（list），無 items/currentPage/perPage/lastPage，且包含全部 30 個角色
        $response->assertOk();
        $data = $response->json('data');
        $this->assertTrue(array_is_list($data));

        $names = collect($data)->pluck('name');
        for ($i = 0; $i < 30; ++$i) {
            $this->assertContains("role_{$i}", $names);
        }
    }

    /** /select 命中 select 而非 show（回 200 陣列，非單筆查詢的 404） */
    public function testSelectRouteIsNotCapturedByShow(): void
    {
        // GIVEN 已登入後台人員
        $actor = $this->admin();

        // WHEN  發送 GET /admin-api/roles/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles/select');

        // THEN  回傳 200 且 data 為陣列（若被 show 以 id='select' 命中會是 404）
        $response->assertOk();
        $this->assertIsArray($response->json('data'));
    }

    /** 不具 view_roles 權限仍可取得（不額外檢查權限） */
    public function testReturnsOkWithoutViewRolesPermission(): void
    {
        // GIVEN 已登入但無任何權限的後台人員
        $actor = $this->admin();

        // WHEN  發送 GET /admin-api/roles/select
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles/select');

        // THEN  回傳 200（不因缺 view_roles 而 403）
        $response->assertOk();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/roles/select
        $response = $this->getJson('/admin-api/roles/select');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 建立一個具角色但無任何權限的已登入後台人員 */
    private function admin(): Admin
    {
        Permission::firstOrCreate(['name' => 'view_roles', 'guard_name' => Guard::ADMIN->value]);
        $role = Role::create(['name' => 'actor_role', 'guard_name' => Guard::ADMIN->value]);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }
}
