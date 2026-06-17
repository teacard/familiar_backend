<?php

namespace Tests\Feature\AdminApi\Admin;

use App\Enums\Admin\Status;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StatusToggleTest extends TestCase
{
    use RefreshDatabase;

    /** 成功將後台人員狀態設為啟用，回傳空陣列 */
    public function testTogglesStatusToActive(): void
    {
        // GIVEN 有 edit_users 權限的管理員，以及一筆狀態為 suspended 的後台人員
        $actor = $this->adminWith('edit_users');
        $target = $this->targetAdmin(['status' => Status::SUSPENDED]);

        // WHEN  發送 PATCH /admin-api/admin/{id}/status，body 為 status=active
        $response = $this->actingAs($actor, 'admin')
            ->patchJson("/admin-api/admin/{$target->id}/status", ['status' => 'active']);

        // THEN  回傳 200，data 為空陣列，DB 中 status 已更新為 active
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseHas('admins', ['id' => $target->id, 'status' => 'active']);
    }

    /** 成功將後台人員狀態設為停用，回傳空陣列 */
    public function testTogglesStatusToSuspended(): void
    {
        // GIVEN 有 edit_users 權限的管理員，以及一筆狀態為 active 的後台人員
        $actor = $this->adminWith('edit_users');
        $target = $this->targetAdmin(['status' => Status::ACTIVE]);

        // WHEN  發送 PATCH /admin-api/admin/{id}/status，body 為 status=suspended
        $response = $this->actingAs($actor, 'admin')
            ->patchJson("/admin-api/admin/{$target->id}/status", ['status' => 'suspended']);

        // THEN  回傳 200，data 為空陣列，DB 中 status 已更新為 suspended
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseHas('admins', ['id' => $target->id, 'status' => 'suspended']);
    }

    /** 設為相同狀態（冪等操作）回傳空陣列，不產生錯誤 */
    public function testIsIdempotent(): void
    {
        // GIVEN 有 edit_users 權限的管理員，以及一筆已停用的後台人員
        $actor = $this->adminWith('edit_users');
        $target = $this->targetAdmin(['status' => Status::SUSPENDED]);

        // WHEN  再次設為 suspended
        $response = $this->actingAs($actor, 'admin')
            ->patchJson("/admin-api/admin/{$target->id}/status", ['status' => 'suspended']);

        // THEN  回傳 200，data 為空陣列，不報錯
        $response->assertOk()
            ->assertJsonPath('data', []);
    }

    /** status 傳入非法值時回傳 422，errors.status 含錯誤訊息 */
    public function testRejectsInvalidStatusValue(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆後台人員
        $actor = $this->adminWith('edit_users');
        $target = $this->targetAdmin();

        // WHEN  status 為 banned（非法值）
        $response = $this->actingAs($actor, 'admin')
            ->patchJson("/admin-api/admin/{$target->id}/status", ['status' => 'banned']);

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** 缺少 status 欄位時回傳 422，errors.status 含錯誤訊息 */
    public function testRejectsMissingStatusField(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆後台人員
        $actor = $this->adminWith('edit_users');
        $target = $this->targetAdmin();

        // WHEN  body 未帶 status 欄位
        $response = $this->actingAs($actor, 'admin')
            ->patchJson("/admin-api/admin/{$target->id}/status", []);

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** 對已軟刪除的後台人員切換狀態回傳 404 */
    public function testReturns404ForSoftDeletedAdmin(): void
    {
        // GIVEN 有 edit_users 權限的管理員，以及一筆已軟刪除的後台人員
        $actor = $this->adminWith('edit_users');
        $target = $this->targetAdmin();
        $target->delete();

        // WHEN  發送 PATCH /admin-api/admin/{id}/status
        $response = $this->actingAs($actor, 'admin')
            ->patchJson("/admin-api/admin/{$target->id}/status", ['status' => 'active']);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 一筆後台人員，未攜帶 Token
        $target = $this->targetAdmin();

        // WHEN  未認證發送請求
        $response = $this->patchJson("/admin-api/admin/{$target->id}/status", ['status' => 'active']);

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 edit_users 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員，及一筆後台人員
        $actor = $this->adminWithNoPermission();
        $target = $this->targetAdmin();

        // WHEN  發送 PATCH /admin-api/admin/{id}/status
        $response = $this->actingAs($actor, 'admin')
            ->patchJson("/admin-api/admin/{$target->id}/status", ['status' => 'active']);

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

    private function targetAdmin(array $attributes = []): Admin
    {
        $role = Role::firstOrCreate(['name' => 'staff_role', 'guard_name' => 'admin']);
        $admin = Admin::factory()->create($attributes);
        $admin->assignRole($role);

        return $admin;
    }
}
