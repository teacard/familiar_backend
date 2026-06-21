<?php

namespace Tests\Feature\AdminApi\Admin;

use App\Enums\Media\CollectionName;
use App\Enums\TemporaryMedia\SystemName;
use App\Models\Admin;
use App\Models\TemporaryMedia;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** 不更換密碼時成功更新，回傳空陣列，密碼保持不變 */
    public function testSucceedsWithoutChangingPassword(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆後台人員
        $actor = $this->adminWith('edit_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $target = Admin::factory()->create(['name' => '舊名字']);
        $target->assignRole($editorRole);
        $originalHash = $target->password;

        // WHEN  更新 name 不帶 password
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => '新名字',
                'email' => $target->email,
                'roleId' => $editorRole->id,
                'status' => 'active',
            ]);

        // THEN  回傳 200，data 為空陣列，name 已更新，密碼 hash 不變
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseHas('admins', ['id' => $target->id, 'name' => '新名字']);
        $this->assertSame($originalHash, $target->fresh()->password);
    }

    /** 帶有效新密碼時成功更新，回傳空陣列 */
    public function testSucceedsWithNewPassword(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆後台人員
        $actor = $this->adminWith('edit_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($editorRole);
        $originalHash = $target->password;

        // WHEN  帶新密碼更新
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'roleId' => $editorRole->id,
                'status' => 'active',
                'password' => 'newpassword123',
                'passwordConfirmation' => 'newpassword123',
            ]);

        // THEN  回傳 200，data 為空陣列，密碼已更新
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertNotSame($originalHash, $target->fresh()->password);
    }

    /** 更新 name 為自身相同名稱時允許（排除自身的 unique 驗證） */
    public function testAllowsSameNameAsSelf(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆後台人員
        $actor = $this->adminWith('edit_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $target = Admin::factory()->create(['name' => '王小明']);
        $target->assignRole($editorRole);

        // WHEN  name 與自身相同
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => '王小明',
                'email' => $target->email,
                'roleId' => $editorRole->id,
                'status' => 'active',
            ]);

        // THEN  回傳 200，data 為空陣列（不因重複自身而報錯）
        $response->assertOk()
            ->assertJsonPath('data', []);
    }

    /** name 與他人重複時回傳 422，errors.name 含錯誤訊息 */
    public function testRejectsDuplicateNameFromOtherAdmin(): void
    {
        // GIVEN 有 edit_users 權限的管理員，「李阿花」已存在
        $actor = $this->adminWith('edit_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $existing = Admin::factory()->create(['name' => '李阿花']);
        $existing->assignRole($staffRole);
        $target = Admin::factory()->create(['name' => '原名字']);
        $target->assignRole($staffRole);

        // WHEN  嘗試更新為他人已使用的 name
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => '李阿花',
                'email' => $target->email,
                'roleId' => $staffRole->id,
                'status' => 'active',
            ]);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** 未提供 roleId 時回傳 422，errors.roleId 含錯誤訊息（admin 必須維持有角色） */
    public function testRejectsWhenRoleNotProvided(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆後台人員
        $actor = $this->adminWith('edit_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($editorRole);

        // WHEN  PUT body 中不包含 roleId 欄位
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => '新名字',
                'email' => $target->email,
                'status' => 'active',
            ]);

        // THEN  回傳 422，errors.roleId 包含驗證失敗訊息（roleId 為必填）
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['roleId']);
    }

    /** 更新已軟刪除的後台人員回傳 404 */
    public function testReturns404ForSoftDeletedAdmin(): void
    {
        // GIVEN 有 edit_users 權限的管理員，以及一筆已軟刪除的後台人員
        $actor = $this->adminWith('edit_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $target->delete();

        // WHEN  發送 PUT /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => '新名字',
                'email' => 'new@admin.com',
                'roleId' => $staffRole->id,
                'status' => 'active',
            ]);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 更新超級管理員回傳 404（受屏蔽，不可編輯） */
    public function testReturns404ForSuperAdmin(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆超級管理員
        $actor = $this->adminWith('edit_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->superAdmin()->create();
        $target->assignRole($staffRole);

        // WHEN  發送 PUT /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => '新名字',
                'email' => 'new@admin.com',
                'roleId' => $staffRole->id,
                'status' => 'active',
            ]);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** mediaId 為 null 時刪除後台人員頭像 */
    public function testDeletesAvatarWhenMediaIdIsNull(): void
    {
        // GIVEN 有 edit_users 權限的管理員，及一筆有頭像的後台人員
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWith('edit_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $target->addMedia(UploadedFile::fake()->image('avatar.png'))
            ->toMediaCollection(CollectionName::ADMIN->value);

        // WHEN  PUT body 中 mediaId 為 null
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'roleId' => $staffRole->id,
                'status' => 'active',
                'mediaId' => null,
            ]);

        // THEN  回傳 200，頭像已刪除
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertCount(0, $target->fresh()->getMedia(CollectionName::ADMIN->value));
    }

    /** mediaId 有效時將暫存媒體轉移至後台人員頭像集合 */
    public function testTransfersTemporaryMediaWhenMediaIdProvided(): void
    {
        // GIVEN 有 edit_users 權限的管理員、後台人員，以及一筆暫存媒體
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $actor = $this->adminWith('edit_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();
        $media = $owner->addMedia(UploadedFile::fake()->image('new.png'))
            ->toMediaCollection(CollectionName::TEMPORARY->value);

        // WHEN  PUT body 帶入暫存媒體 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'roleId' => $staffRole->id,
                'status' => 'active',
                'mediaId' => $media->id,
            ]);

        // THEN  回傳 200，媒體已掛到後台人員的 admin 集合
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'model_type' => (new Admin())->getMorphClass(),
            'model_id' => $target->id,
            'collection_name' => CollectionName::ADMIN->value,
        ]);
    }

    /** 缺少 edit_users 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員，及一筆後台人員
        $actor = $this->adminWithNoPermission();
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);

        // WHEN  發送 PUT /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/admin/{$target->id}", [
                'name' => '新名字',
                'email' => 'new@admin.com',
                'roleId' => $staffRole->id,
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
