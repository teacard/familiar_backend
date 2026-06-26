<?php

namespace Tests\Feature\AdminApi\Announcement;

use App\Enums\Announcement\Status;
use App\Enums\ApiCode;
use App\Models\Admin;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** 合法資料可建立公告，狀態固定為 SCHEDULED，未置頂 order_by 為 UNPINNED_ORDER */
    public function testCreatesAnnouncement(): void
    {
        // GIVEN 有 create_announcements 權限的管理員
        $actor = $this->adminWith('create_announcements');

        // WHEN  送出合法建立請求（未置頂）
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload());

        // THEN  回傳 200，公告以 SCHEDULED、UNPINNED_ORDER 建立
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseHas('announcements', [
            'title' => '系統維護公告',
            'status' => Status::SCHEDULED->value,
            'order_by' => Announcement::UNPINNED_ORDER,
        ]);
    }

    /** shouldPin=true 時建立為置頂，order_by 補到置頂群最後 */
    public function testCreatesPinnedAnnouncement(): void
    {
        // GIVEN 有權限的管理員與一筆既有置頂（order_by=0）
        $actor = $this->adminWith('create_announcements');
        Announcement::factory()->pinned(0)->create();

        // WHEN  送出 shouldPin=true 的建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload(['shouldPin' => true]));

        // THEN  新公告 order_by 補到 1（置頂群最後）
        $response->assertOk();
        $this->assertDatabaseHas('announcements', [
            'title' => '系統維護公告',
            'order_by' => 1,
        ]);
    }

    /** 已有 3 筆置頂時再置頂回傳 422，apiCode 為 PIN_LIMIT_REACHED */
    public function testRejectsPinWhenLimitReached(): void
    {
        // GIVEN 有權限的管理員與 3 筆置頂公告
        $actor = $this->adminWith('create_announcements');
        Announcement::factory()->pinned(0)->create();
        Announcement::factory()->pinned(1)->create();
        Announcement::factory()->pinned(2)->create();

        // WHEN  再建立一筆置頂公告
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload(['shouldPin' => true]));

        // THEN  回傳 422，apiCode 為置頂上限
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::ANNOUNCEMENT_PIN_LIMIT_REACHED->value);
    }

    /** expiresAt 為 null（永久發布）可成功建立 */
    public function testCreatesPermanentAnnouncement(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_announcements');

        // WHEN  送出 expiresAt=null 的建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload(['expiresAt' => null]));

        // THEN  回傳 200，expires_at 為 null
        $response->assertOk();
        $this->assertDatabaseHas('announcements', [
            'title' => '系統維護公告',
            'expires_at' => null,
        ]);
    }

    /** 缺少 title 時回傳 422 */
    public function testRejectsMissingTitle(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_announcements');

        // WHEN  body 不含 title
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload(['title' => null]));

        // THEN  回傳 422，errors.title
        $response->assertUnprocessable()->assertJsonValidationErrors(['title']);
    }

    /** targetAudience 不在 enum 範圍時回傳 422 */
    public function testRejectsInvalidTargetAudience(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_announcements');

        // WHEN  targetAudience 為不合法值
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload(['targetAudience' => 'UNKNOWN']));

        // THEN  回傳 422，errors.targetAudience
        $response->assertUnprocessable()->assertJsonValidationErrors(['targetAudience']);
    }

    /** publishAt 早於現在時回傳 422 */
    public function testRejectsPastPublishAt(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_announcements');

        // WHEN  publishAt 為過去時間
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload([
                'publishAt' => now()->subDay()->format('Y-m-d H:i:s'),
            ]));

        // THEN  回傳 422，errors.publishAt
        $response->assertUnprocessable()->assertJsonValidationErrors(['publishAt']);
    }

    /** expiresAt 早於 publishAt 時回傳 422 */
    public function testRejectsExpiresAtBeforePublishAt(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_announcements');

        // WHEN  expiresAt 早於 publishAt
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload([
                'publishAt' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'expiresAt' => now()->addDay()->format('Y-m-d H:i:s'),
            ]));

        // THEN  回傳 422，errors.expiresAt
        $response->assertUnprocessable()->assertJsonValidationErrors(['expiresAt']);
    }

    /** 缺少 create_announcements 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  送出建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/announcements', $this->payload());

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => '系統維護公告',
            'content' => '系統將於本週日進行維護。',
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
