<?php

namespace Tests\Feature\AdminApi\Role;

use App\Enums\Auth\Guard;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得角色分頁列表，items 每筆含 id、name、isDeletable，不含 permissions，且不含 total */
    public function testReturnsRoleListWithIsDeletable(): void
    {
        // GIVEN 有 view_roles 權限的管理員
        $actor = $this->adminWith('view_roles');

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  回傳 200，data 為分頁結構（items + 分頁欄位），items 每筆含 id、name、isDeletable，且不含 total
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

        $this->assertArrayNotHasKey('total', $response->json('data'));

        foreach ($response->json('data.items') as $entry) {
            $this->assertArrayNotHasKey('permissions', $entry);
            $this->assertIsBool($entry['isDeletable']);
        }
    }

    /** 角色有 admin 帳號使用時，isDeletable 為 false */
    public function testIsDeletableFalseWhenRoleHasAssignedAdmin(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個被某 admin 指派的角色
        $actor = $this->adminWith('view_roles');

        $usedRole = Role::create(['name' => 'used_role', 'guard_name' => Guard::ADMIN->value]);
        Admin::factory()->create()->assignRole($usedRole);

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  used_role 的 isDeletable 為 false
        $response->assertOk();
        $entry = collect($response->json('data.items'))->firstWhere('name', 'used_role');

        $this->assertNotNull($entry);
        $this->assertFalse($entry['isDeletable']);
    }

    /** 角色沒有任何 admin 帳號使用時，isDeletable 為 true */
    public function testIsDeletableTrueWhenNoAdminAssigned(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個無人使用的角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'unused_role', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  unused_role 的 isDeletable 為 true
        $response->assertOk();
        $entry = collect($response->json('data.items'))->firstWhere('name', 'unused_role');

        $this->assertNotNull($entry);
        $this->assertTrue($entry['isDeletable']);
    }

    /** 軟刪除的 admin 不算使用該角色，isDeletable 仍為 true */
    public function testSoftDeletedAdminDoesNotBlockDeletion(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個僅被軟刪除 admin 指派的角色
        $actor = $this->adminWith('view_roles');

        $role = Role::create(['name' => 'orphan_role', 'guard_name' => Guard::ADMIN->value]);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);
        $admin->delete();

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  orphan_role 的 isDeletable 為 true
        $response->assertOk();
        $entry = collect($response->json('data.items'))->firstWhere('name', 'orphan_role');

        $this->assertNotNull($entry);
        $this->assertTrue($entry['isDeletable']);
    }

    /** 只回傳 admin guard 的角色，web guard 角色不出現 */
    public function testOnlyReturnsAdminGuardRoles(): void
    {
        // GIVEN 有 view_roles 權限的管理員，同時存在一個 web guard 角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'web_role', 'guard_name' => Guard::WEB->value]);

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  回傳 200，web_role 不出現在 items 中
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertNotContains('web_role', $names);
    }

    /** keyword 模糊搜尋，只回傳 name 含關鍵字的角色 */
    public function testFiltersRolesByKeyword(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及兩個 name 不同的角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'editor', 'guard_name' => Guard::ADMIN->value]);
        Role::create(['name' => 'viewer', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 GET /admin-api/roles?keyword=edit
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles?keyword=edit');

        // THEN  只回傳 name 含 edit 的角色
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('editor', $names);
        $this->assertNotContains('viewer', $names);
    }

    /** keyword 為 null 時回傳所有 admin guard 角色 */
    public function testReturnsAllRolesWhenKeywordIsAbsent(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及兩個角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'editor', 'guard_name' => Guard::ADMIN->value]);
        Role::create(['name' => 'viewer', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 GET /admin-api/roles（不帶 keyword）
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  兩個角色都出現（actor_role 也會出現，共 3 筆）
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('editor', $names);
        $this->assertContains('viewer', $names);
    }

    /** perPage=25 時每頁最多 25 筆，且 perPage 回傳 25 */
    public function testRespectsPerPage(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及 30 個角色
        $actor = $this->adminWith('view_roles');
        for ($i = 0; $i < 30; ++$i) {
            Role::create(['name' => "role_{$i}", 'guard_name' => Guard::ADMIN->value]);
        }

        // WHEN  帶 perPage=25
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles?perPage=25');

        // THEN  perPage 為 25，且 items 最多 25 筆
        $response->assertOk()
            ->assertJsonPath('data.perPage', 25);
        $this->assertCount(25, $response->json('data.items'));
    }

    /** perPage 傳入不合法值時回傳 422，errors.perPage 含錯誤訊息 */
    public function testRejectsInvalidPerPage(): void
    {
        // GIVEN 有 view_roles 權限的管理員
        $actor = $this->adminWith('view_roles');

        // WHEN  傳入 perPage=100
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles?perPage=100');

        // THEN  回傳 422，errors.perPage 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['perPage']);
    }

    /** 系統角色（is_system=true）不出現在列表 */
    public function testExcludesSystemRole(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個系統角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'system_role', 'guard_name' => Guard::ADMIN->value, 'is_system' => true]);

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  回傳 200，system_role 不出現在 items 中
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertNotContains('system_role', $names);
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/roles
        $response = $this->getJson('/admin-api/roles');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 view_roles 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  回傳 403
        $response->assertForbidden();
    }

    private function adminPermission(string $name): Permission
    {
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => Guard::ADMIN->value]);
    }

    private function adminWith(string ...$permissions): Admin
    {
        foreach ($permissions as $p) {
            $this->adminPermission($p);
        }
        $role = Role::create(['name' => 'actor_role', 'guard_name' => Guard::ADMIN->value]);
        $role->givePermissionTo($permissions);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }

    private function adminWithNoPermission(): Admin
    {
        $role = Role::create(['name' => 'empty_role', 'guard_name' => Guard::ADMIN->value]);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }
}
