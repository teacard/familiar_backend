<?php

namespace Tests\Feature\AdminApi\Role;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得角色列表，每筆含 id、name、permissions */
    public function testReturnsRoleListWithPermissions(): void
    {
        // GIVEN 有 view_roles 權限的管理員，以及另一個持有 view_users 權限的 admin guard 角色
        $this->adminPermission('view_users');
        $actor = $this->adminWith('view_roles');

        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $editorRole->givePermissionTo('view_users');

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  回傳 200，data 含角色結構
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'permissions' => [
                            '*' => ['name', 'label'],
                        ],
                    ],
                ],
            ]);
    }

    /** 角色持有多個 permission 時，permissions 含完整清單及 label */
    public function testIncludesAllPermissionsWithLabel(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個持有兩個權限的角色
        $this->adminPermission('view_users');
        $this->adminPermission('edit_users');
        $actor = $this->adminWith('view_roles');

        $superRole = Role::create(['name' => 'super', 'guard_name' => 'admin']);
        $superRole->givePermissionTo(['view_users', 'edit_users']);

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  找到 super 角色，其 permissions 含兩筆，每筆有 name 與 label
        $response->assertOk();
        $roles = collect($response->json('data'));
        $superEntry = $roles->firstWhere('name', 'super');

        $this->assertNotNull($superEntry);
        $this->assertCount(2, $superEntry['permissions']);
        $permNames = collect($superEntry['permissions'])->pluck('name');
        $this->assertContains('view_users', $permNames);
        $this->assertContains('edit_users', $permNames);

        foreach ($superEntry['permissions'] as $perm) {
            $this->assertArrayHasKey('label', $perm);
            $this->assertNotEmpty($perm['label']);
        }
    }

    /** 角色沒有任何 permission 時 permissions 為空陣列 */
    public function testReturnsEmptyPermissionsForRoleWithNone(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個無任何權限的角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'no_perm_role', 'guard_name' => 'admin']);

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  no_perm_role 的 permissions 為空陣列
        $response->assertOk();
        $roles = collect($response->json('data'));
        $emptyEntry = $roles->firstWhere('name', 'no_perm_role');

        $this->assertNotNull($emptyEntry);
        $this->assertSame([], $emptyEntry['permissions']);
    }

    /** 只回傳 admin guard 的角色，web guard 角色不出現 */
    public function testOnlyReturnsAdminGuardRoles(): void
    {
        // GIVEN 有 view_roles 權限的管理員，同時存在一個 web guard 角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'web_role', 'guard_name' => 'web']);

        // WHEN  發送 GET /admin-api/roles
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  回傳 200，web_role 不出現在 data 中
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertNotContains('web_role', $names);
    }

    /** keyword 模糊搜尋，只回傳 name 含關鍵字的角色 */
    public function testFiltersRolesByKeyword(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及兩個 name 不同的角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        Role::create(['name' => 'viewer', 'guard_name' => 'admin']);

        // WHEN  發送 GET /admin-api/roles?keyword=edit
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles?keyword=edit');

        // THEN  只回傳 name 含 edit 的角色
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('editor', $names);
        $this->assertNotContains('viewer', $names);
    }

    /** keyword 為 null 時回傳所有 admin guard 角色 */
    public function testReturnsAllRolesWhenKeywordIsAbsent(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及兩個角色
        $actor = $this->adminWith('view_roles');
        Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        Role::create(['name' => 'viewer', 'guard_name' => 'admin']);

        // WHEN  發送 GET /admin-api/roles（不帶 keyword）
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles');

        // THEN  兩個角色都出現（actor_role 也會出現，共 3 筆）
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('editor', $names);
        $this->assertContains('viewer', $names);
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
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
    }

    private function adminWith(string ...$permissions): Admin
    {
        foreach ($permissions as $p) {
            $this->adminPermission($p);
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
