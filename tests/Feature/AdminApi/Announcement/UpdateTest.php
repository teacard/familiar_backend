<?php

namespace Tests\Feature\AdminApi\Announcement;

use App\Enums\ApiCode;
use App\Models\Admin;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** SCHEDULED 公告可更新標題與內容 */
    public function testUpdatesScheduledAnnouncement(): void
    {
        // GIVEN 有 edit_announcements 權限的管理員與一筆 SCHEDULED 公告
        $actor = $this->adminWith('edit_announcements');
        $announcement = Announcement::factory()->create(['title' => '舊標題']);

        // WHEN  送出更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/announcements/{$announcement->id}", $this->payload(['title' => '新標題']));

        // THEN  回傳 200，標題已更新
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'title' => '新標題']);
    }

    /** 已發佈公告變更不可改欄位時回傳 422（apiCode 為 IMMUTABLE） */
    public function testRejectsImmutableChangeOnPublished(): void
    {
        // GIVEN 有權限的管理員與一筆已發佈公告（publish_at 在過去）
        $actor = $this->adminWith('edit_announcements');
        $announcement = Announcement::factory()->published()->create();

        // WHEN  送出會變更 publishAt 的更新（未來時間，與原本不同）
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/announcements/{$announcement->id}", $this->payload());

        // THEN  回傳 422，apiCode 為已發佈不可修改
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::ANNOUNCEMENT_PUBLISHED_IMMUTABLE->value);
    }

    /** 透過更新將公告設為置頂 */
    public function testPinsViaUpdate(): void
    {
        // GIVEN 有權限的管理員與一筆未置頂公告
        $actor = $this->adminWith('edit_announcements');
        $announcement = Announcement::factory()->create();

        // WHEN  送出 shouldPin=true 的更新
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/announcements/{$announcement->id}", $this->payload(['shouldPin' => true]));

        // THEN  公告變為置頂（order_by < TOP_LIMIT）
        $response->assertOk();
        $this->assertSame(0, $announcement->fresh()->order_by);
    }

    /** 透過更新取消置頂後，其後的置頂往前補位 */
    public function testUnpinCompactsFollowingPinned(): void
    {
        // GIVEN 有權限的管理員與 3 筆置頂公告（order_by 0,1,2）
        $actor = $this->adminWith('edit_announcements');
        $first = Announcement::factory()->pinned(0)->create();
        $middle = Announcement::factory()->pinned(1)->create();
        $last = Announcement::factory()->pinned(2)->create();

        // WHEN  將中間那筆取消置頂
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/announcements/{$middle->id}", $this->payload(['shouldPin' => false]));

        // THEN  middle 變為未置頂，last 從 2 補到 1，first 不動
        $response->assertOk();
        $this->assertGreaterThanOrEqual(Announcement::TOP_LIMIT, $middle->fresh()->order_by);
        $this->assertSame(0, $first->fresh()->order_by);
        $this->assertSame(1, $last->fresh()->order_by);
    }

    /** 查無公告時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('edit_announcements');

        // WHEN  更新不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson('/admin-api/announcements/99999', $this->payload());

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 edit_announcements 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員與一筆公告
        $actor = $this->adminWithNoPermission();
        $announcement = Announcement::factory()->create();

        // WHEN  送出更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/announcements/{$announcement->id}", $this->payload());

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => '更新後標題',
            'content' => '更新後內容',
            'targetAudience' => 'ALL_USERS',
            'publishAt' => now()->addDay()->format('Y-m-d H:i:s'),
            'expiresAt' => now()->addDays(8)->format('Y-m-d H:i:s'),
            'shouldPin' => false,
        ], $overrides);
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
