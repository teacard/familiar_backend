<?php

namespace Tests\Feature\AdminApi\Admin;

use App\Enums\Media\CollectionName;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** 成功硬刪除後台人員，回傳空陣列，資料列被移除 */
    public function testDeletesAdmin(): void
    {
        // GIVEN 有 delete_users 權限的管理員，及一筆待刪除的後台人員
        $actor = $this->adminWith('delete_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);

        // WHEN  發送 DELETE /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/admin/{$target->id}");

        // THEN  回傳 200，data 為空陣列，資料列已從 admins 移除
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseMissing('admins', ['id' => $target->id]);
    }

    /** 軟刪除後，列表不再顯示該後台人員 */
    public function testDeletedAdminDisappearsFromList(): void
    {
        // GIVEN 有 delete_users + view_users 權限的管理員，及一筆後台人員
        $actor = $this->adminWith('delete_users', 'view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);

        // WHEN  刪除後查詢列表
        $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/admin/{$target->id}");

        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin');

        // THEN  已刪除的後台人員不出現在列表
        $ids = collect($response->json('data.items'))->pluck('id');
        $this->assertNotContains($target->id, $ids);
    }

    /** 刪除已被刪除的後台人員回傳 404 */
    public function testReturns404ForAlreadyDeletedAdmin(): void
    {
        // GIVEN 有 delete_users 權限的管理員，及一筆已被刪除的後台人員
        $actor = $this->adminWith('delete_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $target->delete();

        // WHEN  再次刪除
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/admin/{$target->id}");

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 刪除超級管理員回傳 404（受屏蔽，不可刪除） */
    public function testReturns404ForSuperAdmin(): void
    {
        // GIVEN 有 delete_users 權限的管理員，及一筆超級管理員
        $actor = $this->adminWith('delete_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->superAdmin()->create();
        $target->assignRole($staffRole);

        // WHEN  發送 DELETE /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/admin/{$target->id}");

        // THEN  回傳 404，且資料未被刪除
        $response->assertNotFound();
        $this->assertDatabaseHas('admins', ['id' => $target->id]);
    }

    /** 刪除後台人員時，其頭像媒體與實體檔案一併被清除 */
    public function testDestroyAlsoDeletesAdminMedia(): void
    {
        // GIVEN 已有頭像的後台人員
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWith('delete_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $avatarMedia = $target->addMedia(UploadedFile::fake()->image('avatar.png'))
            ->toMediaCollection(CollectionName::ADMIN->value);
        $path = $avatarMedia->getPathRelativeToRoot();

        Storage::disk('minio')->assertExists($path);

        // WHEN  刪除該後台人員
        $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/admin/{$target->id}")
            ->assertOk();

        // THEN  media 紀錄與實體檔案皆被刪除
        $this->assertDatabaseMissing('media', ['id' => $avatarMedia->id]);
        Storage::disk('minio')->assertMissing($path);
    }

    /** 缺少 delete_users 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員，及一筆後台人員
        $actor = $this->adminWithNoPermission();
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);

        // WHEN  發送 DELETE /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/admin/{$target->id}");

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
