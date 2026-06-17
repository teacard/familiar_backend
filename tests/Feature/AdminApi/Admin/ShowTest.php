<?php

namespace Tests\Feature\AdminApi\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 存在的 id 可取得後台人員完整資料 */
    public function testReturnsAdminData(): void
    {
        // GIVEN 有 view_users 權限的管理員，及一筆待查詢的後台人員
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);

        // WHEN  發送 GET /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/admin/{$target->id}");

        // THEN  回傳 200 含完整欄位
        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role', 'status', 'lastLoginDate']])
            ->assertJsonPath('data.id', $target->id);
    }

    /** 不存在的 id 回傳 404 */
    public function testReturns404ForNonexistentId(): void
    {
        // GIVEN 有 view_users 權限的管理員
        $actor = $this->adminWith('view_users');

        // WHEN  查詢不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin/99999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 已軟刪除的後台人員回傳 404 */
    public function testReturns404ForSoftDeletedAdmin(): void
    {
        // GIVEN 有 view_users 權限的管理員，以及一筆已軟刪除的後台人員
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $target->delete();

        // WHEN  查詢已軟刪除的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/admin/{$target->id}");

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 view_users 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);

        // WHEN  發送 GET /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/admin/{$target->id}");

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
