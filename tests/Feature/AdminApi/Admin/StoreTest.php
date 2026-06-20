<?php

namespace Tests\Feature\AdminApi\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** 合法資料可成功建立後台人員，回傳空陣列，狀態依請求帶入 */
    public function testCreatesAdmin(): void
    {
        // GIVEN 有 create_users 權限的管理員，以及一個現有的 editor 角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  送出合法建立請求（狀態為 active）
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'zhang@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $editorRole->id,
                'status' => 'active',
            ]);

        // THEN  回傳 200，data 為空陣列，後台人員已建立且狀態為 active
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseHas('admins', ['email' => 'zhang@admin.com', 'status' => 'active']);
    }

    /** 建立時依請求帶入的狀態寫入（suspended） */
    public function testCreatesAdminWithSuspendedStatus(): void
    {
        // GIVEN 有 create_users 權限的管理員，以及一個現有的 editor 角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  送出狀態為 suspended 的建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '李四',
                'email' => 'li@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $editorRole->id,
                'status' => 'suspended',
            ]);

        // THEN  回傳 200，後台人員已建立且狀態為 suspended
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseHas('admins', ['email' => 'li@admin.com', 'status' => 'suspended']);
    }

    /** 未提供 status 時回傳 422，errors.status 含錯誤訊息（status 為必填） */
    public function testRejectsWhenStatusNotProvided(): void
    {
        // GIVEN 有 create_users 權限的管理員，及一個現有角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  body 中不包含 status 欄位
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'test@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $editorRole->id,
            ]);

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** status 不在 enum 範圍時回傳 422，errors.status 含錯誤訊息 */
    public function testRejectsInvalidStatus(): void
    {
        // GIVEN 有 create_users 權限的管理員，及一個現有角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  status 傳入不合法的值
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'test@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $editorRole->id,
                'status' => 'unknown',
            ]);

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** name 超過 15 字元時回傳 422，errors.name 含錯誤訊息 */
    public function testRejectsNameExceeding15Chars(): void
    {
        // GIVEN 有 create_users 權限的管理員，及一個現有角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  name 為 16 字元
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => str_repeat('a', 16),
                'email' => 'test@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $editorRole->id,
            ]);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** name 與現有後台人員重複時回傳 422，errors.name 含錯誤訊息 */
    public function testRejectsDuplicateName(): void
    {
        // GIVEN 有 create_users 權限的管理員，且「張三」已存在
        $actor = $this->adminWith('create_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $existing = Admin::factory()->create(['name' => '張三']);
        $existing->assignRole($staffRole);

        // WHEN  嘗試再建立同名後台人員
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'new@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $staffRole->id,
            ]);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** email 格式錯誤時回傳 422，errors.email 含錯誤訊息 */
    public function testRejectsInvalidEmailFormat(): void
    {
        // GIVEN 有 create_users 權限的管理員，及一個現有角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  email 格式不正確
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'not-an-email',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $editorRole->id,
            ]);

        // THEN  回傳 422，errors.email 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** email 與現有後台人員重複時回傳 422，errors.email 含錯誤訊息 */
    public function testRejectsDuplicateEmail(): void
    {
        // GIVEN 有 create_users 權限的管理員，且該 email 已存在
        $actor = $this->adminWith('create_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $existing = Admin::factory()->create(['email' => 'dup@admin.com']);
        $existing->assignRole($staffRole);

        // WHEN  嘗試使用相同 email 建立
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'dup@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => $staffRole->id,
            ]);

        // THEN  回傳 422，errors.email 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** 密碼少於 8 字元時回傳 422，errors.password 含錯誤訊息 */
    public function testRejectsShortPassword(): void
    {
        // GIVEN 有 create_users 權限的管理員，及一個現有角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  password 只有 7 字元
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'test@admin.com',
                'password' => '1234567',
                'passwordConfirmation' => '1234567',
                'roleId' => $editorRole->id,
            ]);

        // THEN  回傳 422，errors.password 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /** 密碼與確認密碼不一致時回傳 422，errors.password 含錯誤訊息 */
    public function testRejectsMismatchedPasswords(): void
    {
        // GIVEN 有 create_users 權限的管理員，及一個現有角色
        $actor = $this->adminWith('create_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);

        // WHEN  password 與 passwordConfirmation 不同
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'test@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'different123',
                'roleId' => $editorRole->id,
            ]);

        // THEN  回傳 422，errors.password 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /** roleId 不存在時回傳 422，errors.roleId 含錯誤訊息 */
    public function testRejectsNonexistentRole(): void
    {
        // GIVEN 有 create_users 權限的管理員
        $actor = $this->adminWith('create_users');

        // WHEN  roleId 為不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'test@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => 99999,
            ]);

        // THEN  回傳 422，errors.roleId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['roleId']);
    }

    /** 未提供 roleId 時回傳 422，errors.roleId 含錯誤訊息（admin 必須有角色） */
    public function testRejectsWhenRoleNotProvided(): void
    {
        // GIVEN 有 create_users 權限的管理員
        $actor = $this->adminWith('create_users');

        // WHEN  body 中不包含 roleId 欄位
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'test@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 422，errors.roleId 包含驗證失敗訊息（roleId 為必填）
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['roleId']);
    }

    /** 缺少 create_users 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送 POST /admin-api/admin（帶合法 status 以確保驗證通過，測到權限檢查）
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/admin', [
                'name' => '張三',
                'email' => 'test@admin.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
                'roleId' => 1,
                'status' => 'active',
            ]);

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
