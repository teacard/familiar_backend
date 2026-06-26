<?php

namespace Tests\Feature\AdminApi\Announcement;

use App\Models\Admin;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 可取得公告詳情（含 targetAudience，不含 id/status） */
    public function testReturnsAnnouncement(): void
    {
        // GIVEN 有 view_announcements 權限的管理員與一筆公告
        $actor = $this->adminWith('view_announcements');
        $announcement = Announcement::factory()->create(['title' => '系統維護公告']);

        // WHEN  發送 GET /admin-api/announcements/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/announcements/{$announcement->id}");

        // THEN  回傳 200，含詳情欄位
        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['title', 'content', 'targetAudience', 'publishAt', 'expiresAt', 'isPinned'],
            ])
            ->assertJsonPath('data.title', '系統維護公告');
    }

    /** 查無公告時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('view_announcements');

        // WHEN  查詢不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements/99999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 view_announcements 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員與一筆公告
        $actor = $this->adminWithNoPermission();
        $announcement = Announcement::factory()->create();

        // WHEN  發送詳情請求
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/announcements/{$announcement->id}");

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
