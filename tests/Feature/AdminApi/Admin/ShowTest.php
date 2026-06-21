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

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 存在的 id 可取得後台人員詳情（name、email、role、status、avatar，不含 id、lastLoginDate） */
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

        // THEN  回傳 200 含詳情欄位，avatar 為 null；不含 id 與 lastLoginDate
        $response->assertOk()
            ->assertJsonStructure(['data' => ['name', 'email', 'role', 'status', 'avatar']])
            ->assertJsonPath('data.name', $target->name)
            ->assertJsonPath('data.avatar', null);

        $this->assertArrayNotHasKey('id', $response->json('data'));
        $this->assertArrayNotHasKey('lastLoginDate', $response->json('data'));
    }

    /** 有頭像時 avatar 回傳含 id 與 url 的物件 */
    public function testReturnsAvatarObjectWhenAvatarExists(): void
    {
        // GIVEN 有 view_users 權限的管理員，及一筆有頭像的後台人員
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $media = $target->addMedia(UploadedFile::fake()->image('avatar.png'))
            ->toMediaCollection(CollectionName::ADMIN->value);

        // WHEN  發送 GET /admin-api/admin/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/admin/{$target->id}");

        // THEN  回傳 200，avatar 含 id 與 url
        $response->assertOk()
            ->assertJsonStructure(['data' => ['avatar' => ['id', 'url']]])
            ->assertJsonPath('data.avatar.id', $media->id);
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

    /** 已刪除的後台人員回傳 404 */
    public function testReturns404ForDeletedAdmin(): void
    {
        // GIVEN 有 view_users 權限的管理員，以及一筆已刪除的後台人員
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create();
        $target->assignRole($staffRole);
        $target->delete();

        // WHEN  查詢已刪除的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/admin/{$target->id}");

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 查詢超級管理員回傳 404（受屏蔽） */
    public function testReturns404ForSuperAdmin(): void
    {
        // GIVEN 有 view_users 權限的管理員，及一筆超級管理員
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->superAdmin()->create();
        $target->assignRole($staffRole);

        // WHEN  查詢該超級管理員的 id
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
