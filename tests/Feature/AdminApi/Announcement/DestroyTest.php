<?php

namespace Tests\Feature\AdminApi\Announcement;

use App\Enums\ApiCode;
use App\Models\Admin;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** 非已發佈公告可成功刪除 */
    public function testDeletesAnnouncement(): void
    {
        // GIVEN 有 delete_announcements 權限的管理員與一筆 SCHEDULED 公告
        $actor = $this->adminWith('delete_announcements');
        $announcement = Announcement::factory()->create();

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/announcements/{$announcement->id}");

        // THEN  回傳 200，資料已刪除
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    /** 已發佈公告禁止刪除，回傳 422（apiCode 為 UNDELETABLE） */
    public function testRejectsDeletingPublished(): void
    {
        // GIVEN 有權限的管理員與一筆已發佈公告
        $actor = $this->adminWith('delete_announcements');
        $announcement = Announcement::factory()->published()->create();

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/announcements/{$announcement->id}");

        // THEN  回傳 422，apiCode 為已發佈不可刪除，資料仍存在
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::ANNOUNCEMENT_PUBLISHED_UNDELETABLE->value);
        $this->assertDatabaseHas('announcements', ['id' => $announcement->id]);
    }

    /** 查無公告時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('delete_announcements');

        // WHEN  刪除不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson('/admin-api/announcements/99999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 delete_announcements 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員與一筆公告
        $actor = $this->adminWithNoPermission();
        $announcement = Announcement::factory()->create();

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/announcements/{$announcement->id}");

        // THEN  回傳 403
        $response->assertForbidden();
    }

    private function adminWith(string ...$permissions): Admin
    {
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'admin']);
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
