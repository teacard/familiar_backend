<?php

namespace Tests\Feature\AdminApi\Announcement;

use App\Enums\Announcement\Status;
use App\Models\Admin;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 無篩選時可取得公告分頁列表 */
    public function testReturnsAnnouncementList(): void
    {
        // GIVEN 有 view_announcements 權限的管理員與 3 筆公告
        $actor = $this->adminWith('view_announcements');
        Announcement::factory()->count(3)->create();

        // WHEN  發送 GET /admin-api/announcements
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements');

        // THEN  回傳 200 並含分頁結構（items 含 id，不含 total）
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [['id', 'title', 'content', 'status', 'publishAt', 'expiresAt', 'isPinned']],
                    'currentPage',
                    'perPage',
                    'lastPage',
                ],
            ]);

        $this->assertCount(3, $response->json('data.items'));
        $this->assertArrayNotHasKey('total', $response->json('data'));
    }

    /** 關鍵字篩選只回傳標題含關鍵字的公告 */
    public function testFiltersByKeyword(): void
    {
        // GIVEN 有權限的管理員與兩筆公告（一筆標題含「維護」）
        $actor = $this->adminWith('view_announcements');
        $target = Announcement::factory()->create(['title' => '系統維護公告']);
        Announcement::factory()->create(['title' => '活動開跑']);

        // WHEN  帶 keyword=維護
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements?keyword=維護');

        // THEN  items 只含目標公告
        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
    }

    /** 狀態篩選只回傳對應狀態的公告 */
    public function testFiltersByStatus(): void
    {
        // GIVEN 有權限的管理員與 SCHEDULED / PUBLISHED 各一筆
        $actor = $this->adminWith('view_announcements');
        Announcement::factory()->create(['status' => Status::SCHEDULED]);
        $published = Announcement::factory()->published()->create();

        // WHEN  帶 status=PUBLISHED
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements?status=' . Status::PUBLISHED->value);

        // THEN  items 只含已發布那筆
        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame($published->id, $items[0]['id']);
    }

    /** pinned=true 只回傳置頂公告，pinned=false 只回傳未置頂公告 */
    public function testFiltersByPinned(): void
    {
        // GIVEN 有權限的管理員與一筆置頂、一筆未置頂公告
        $actor = $this->adminWith('view_announcements');
        $pinned = Announcement::factory()->pinned(0)->create();
        $unpinned = Announcement::factory()->create();

        // WHEN  帶 pinned=true
        $pinnedResponse = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements?pinned=true');

        // THEN  只含置頂那筆
        $pinnedResponse->assertOk();
        $pinnedItems = $pinnedResponse->json('data.items');
        $this->assertCount(1, $pinnedItems);
        $this->assertSame($pinned->id, $pinnedItems[0]['id']);

        // WHEN  帶 pinned=false
        $unpinnedResponse = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements?pinned=false');

        // THEN  只含未置頂那筆
        $unpinnedResponse->assertOk();
        $unpinnedItems = $unpinnedResponse->json('data.items');
        $this->assertCount(1, $unpinnedItems);
        $this->assertSame($unpinned->id, $unpinnedItems[0]['id']);
    }

    /** 列表依 created_at 由新到舊排序 */
    public function testOrdersByCreatedAtDesc(): void
    {
        // GIVEN 有權限的管理員與兩筆建立時間不同的公告
        $actor = $this->adminWith('view_announcements');
        $old = Announcement::factory()->create(['created_at' => now()->subDays(2)]);
        $new = Announcement::factory()->create(['created_at' => now()]);

        // WHEN  發送列表請求
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements');

        // THEN  最新的在前
        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertSame($new->id, $items[0]['id']);
        $this->assertSame($old->id, $items[1]['id']);
    }

    /** 未登入時回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 不需任何資料
        // WHEN  未帶 Token 發送請求
        $response = $this->getJson('/admin-api/announcements');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 view_announcements 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送列表請求
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/announcements');

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
