<?php

namespace Tests\Feature\AdminApi\Role;

use App\Enums\Auth\Guard;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 存在的一般角色可取得詳情（name、permissions） */
    public function testReturnsRoleDetail(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個持有 view_users 的角色
        $this->adminPermission('view_users');
        $actor = $this->adminWith('view_roles');
        $role = Role::create(['name' => 'editor', 'guard_name' => Guard::ADMIN->value]);
        $role->givePermissionTo('view_users');

        // WHEN  發送 GET /admin-api/roles/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/roles/{$role->id}");

        // THEN  回傳 200，含 name 與 permissions 結構
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'permissions' => [
                        '*' => ['name', 'label'],
                    ],
                ],
            ])
            ->assertJsonPath('data.name', 'editor');
    }

    /** 查詢系統角色（is_system=true）回傳 404（受屏蔽） */
    public function testReturns404ForSystemRole(): void
    {
        // GIVEN 有 view_roles 權限的管理員，及一個系統角色
        $actor = $this->adminWith('view_roles');
        $systemRole = Role::create(['name' => 'system_role', 'guard_name' => Guard::ADMIN->value, 'is_system' => true]);

        // WHEN  查詢該系統角色的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/roles/{$systemRole->id}");

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 不存在的 id 回傳 404 */
    public function testReturns404ForNonexistentId(): void
    {
        // GIVEN 有 view_roles 權限的管理員
        $actor = $this->adminWith('view_roles');

        // WHEN  查詢不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/roles/99999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/roles/1
        $response = $this->getJson('/admin-api/roles/1');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 view_roles 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員，及一個角色
        $actor = $this->adminWithNoPermission();
        $role = Role::create(['name' => 'editor', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 GET /admin-api/roles/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/roles/{$role->id}");

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
