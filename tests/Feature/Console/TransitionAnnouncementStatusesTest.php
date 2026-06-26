<?php

namespace Tests\Feature\Console;

use App\Enums\Announcement\Status;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransitionAnnouncementStatusesTest extends TestCase
{
    use RefreshDatabase;

    /** 發布時間已到的 SCHEDULED 公告會轉為 PUBLISHED */
    public function testPublishesDueScheduledAnnouncements(): void
    {
        // GIVEN 一筆發布時間已過的 SCHEDULED 公告
        $announcement = Announcement::factory()->create([
            'status' => Status::SCHEDULED,
            'publish_at' => now()->subMinute(),
            'expires_at' => now()->addDays(7),
        ]);

        // WHEN  執行轉換 command
        $this->artisan('announcements:transition-statuses')->assertSuccessful();

        // THEN  狀態變為 PUBLISHED
        $this->assertSame(Status::PUBLISHED, $announcement->fresh()->status);
    }

    /** 到期時間已到的 PUBLISHED 公告會轉為 EXPIRED */
    public function testExpiresDuePublishedAnnouncements(): void
    {
        // GIVEN 一筆到期時間已過的 PUBLISHED 公告
        $announcement = Announcement::factory()->create([
            'status' => Status::PUBLISHED,
            'publish_at' => now()->subDays(10),
            'expires_at' => now()->subMinute(),
        ]);

        // WHEN  執行轉換 command
        $this->artisan('announcements:transition-statuses')->assertSuccessful();

        // THEN  狀態變為 EXPIRED
        $this->assertSame(Status::EXPIRED, $announcement->fresh()->status);
    }

    /** 發布與到期都已過的 SCHEDULED 公告，同一次執行後落在 EXPIRED */
    public function testTransitionsFullyPastScheduledToExpired(): void
    {
        // GIVEN 一筆發布與到期都已過的 SCHEDULED 公告
        $announcement = Announcement::factory()->create([
            'status' => Status::SCHEDULED,
            'publish_at' => now()->subDays(2),
            'expires_at' => now()->subMinute(),
        ]);

        // WHEN  執行轉換 command
        $this->artisan('announcements:transition-statuses')->assertSuccessful();

        // THEN  狀態最終為 EXPIRED
        $this->assertSame(Status::EXPIRED, $announcement->fresh()->status);
    }

    /** 永久發布（expires_at 為 null）的 PUBLISHED 公告不會被轉為 EXPIRED */
    public function testKeepsPermanentPublishedAnnouncement(): void
    {
        // GIVEN 一筆永久發布的 PUBLISHED 公告
        $announcement = Announcement::factory()->published()->permanent()->create();

        // WHEN  執行轉換 command
        $this->artisan('announcements:transition-statuses')->assertSuccessful();

        // THEN  狀態仍為 PUBLISHED
        $this->assertSame(Status::PUBLISHED, $announcement->fresh()->status);
    }

    /** 發布時間未到的 SCHEDULED 公告維持 SCHEDULED */
    public function testKeepsFutureScheduledAnnouncement(): void
    {
        // GIVEN 一筆發布時間在未來的 SCHEDULED 公告
        $announcement = Announcement::factory()->create([
            'status' => Status::SCHEDULED,
            'publish_at' => now()->addDay(),
            'expires_at' => now()->addDays(8),
        ]);

        // WHEN  執行轉換 command
        $this->artisan('announcements:transition-statuses')->assertSuccessful();

        // THEN  狀態仍為 SCHEDULED
        $this->assertSame(Status::SCHEDULED, $announcement->fresh()->status);
    }
}
