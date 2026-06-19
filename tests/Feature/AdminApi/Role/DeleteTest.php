<?php

namespace Tests\Feature\AdminApi\Role;

use App\Enums\Auth\Guard;
use App\Enums\ApiCode;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** 無人使用的角色可成功刪除，回傳 200 且資料庫已移除 */
    public function testDeletesRoleSuccessfullyWhenNoAdminAssigned(): void
    {
        // GIVEN 有 assign_roles 權限的管理員，及一個無人使用的角色
        $actor = $this->adminWith('assign_roles');
        $role = Role::create(['name' => 'unused_role', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 DELETE /admin-api/roles/{id}
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/roles/{$role->id}");

        // THEN  回傳 200，角色已自資料庫移除
        $response->assertOk();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /** 角色已被 admin 帳號使用時，回傳 422 及 ROLE_IN_USE，且角色未被刪除 */
    public function testReturns422WhenRoleInUse(): void
    {
        // GIVEN 有 assign_roles 權限的管理員，及一個被某 admin 指派的角色
        $actor = $this->adminWith('assign_roles');
        $usedRole = Role::create(['name' => 'used_role', 'guard_name' => Guard::ADMIN->value]);
        Admin::factory()->create()->assignRole($usedRole);

        // WHEN  發送 DELETE /admin-api/roles/{id}
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/roles/{$usedRole->id}");

        // THEN  回傳 422，apiCode 為 ROLE_IN_USE，角色仍存在
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::ROLE_IN_USE->value);
        $this->assertDatabaseHas('roles', ['id' => $usedRole->id]);
    }

    /** 僅被軟刪除 admin 指派的角色可成功刪除 */
    public function testDeletesRoleWhenOnlySoftDeletedAdminAssigned(): void
    {
        // GIVEN 有 assign_roles 權限的管理員，及一個僅被軟刪除 admin 指派的角色
        $actor = $this->adminWith('assign_roles');
        $role = Role::create(['name' => 'orphan_role', 'guard_name' => Guard::ADMIN->value]);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);
        $admin->delete();

        // WHEN  發送 DELETE /admin-api/roles/{id}
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/roles/{$role->id}");

        // THEN  回傳 200，角色已自資料庫移除
        $response->assertOk();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /** 查無角色時回傳 404 */
    public function testReturns404WhenRoleNotFound(): void
    {
        // GIVEN 有 assign_roles 權限的管理員
        $actor = $this->adminWith('assign_roles');

        // WHEN  發送 DELETE 不存在的角色 id
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson('/admin-api/roles/999999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token，及一個角色
        $role = Role::create(['name' => 'some_role', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 DELETE /admin-api/roles/{id}
        $response = $this->deleteJson("/admin-api/roles/{$role->id}");

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 assign_roles 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無 assign_roles 權限的管理員，及一個角色
        $actor = $this->adminWithNoPermission();
        $role = Role::create(['name' => 'some_role', 'guard_name' => Guard::ADMIN->value]);

        // WHEN  發送 DELETE /admin-api/roles/{id}
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/roles/{$role->id}");

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
