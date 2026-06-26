<?php

namespace Tests\Feature\Console;

use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneExpiredAnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    /** 到期超過 6 個月的公告會被刪除 */
    public function testDeletesAnnouncementsExpiredOverSixMonths(): void
    {
        // GIVEN 一筆到期已超過 6 個月的公告
        $stale = Announcement::factory()->expired()->create([
            'expires_at' => now()->subMonths(7),
        ]);

        // WHEN  執行清除 command
        $this->artisan('announcements:prune-expired')->assertSuccessful();

        // THEN  該公告被刪除
        $this->assertDatabaseMissing('announcements', ['id' => $stale->id]);
    }

    /** 到期未滿 6 個月的公告會保留 */
    public function testKeepsAnnouncementsExpiredWithinSixMonths(): void
    {
        // GIVEN 一筆到期約 5 個月的公告
        $recent = Announcement::factory()->expired()->create([
            'expires_at' => now()->subMonths(5),
        ]);

        // WHEN  執行清除 command
        $this->artisan('announcements:prune-expired')->assertSuccessful();

        // THEN  該公告仍存在
        $this->assertDatabaseHas('announcements', ['id' => $recent->id]);
    }

    /** 永久發布（expires_at 為 null）的公告不會被刪除 */
    public function testKeepsPermanentAnnouncement(): void
    {
        // GIVEN 一筆永久發布的公告
        $permanent = Announcement::factory()->published()->permanent()->create();

        // WHEN  執行清除 command
        $this->artisan('announcements:prune-expired')->assertSuccessful();

        // THEN  該公告仍存在
        $this->assertDatabaseHas('announcements', ['id' => $permanent->id]);
    }
}
