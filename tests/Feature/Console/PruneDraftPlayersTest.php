<?php

namespace Tests\Feature\Console;

use App\Enums\Media\CollectionName;
use App\Models\DraftPlayer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneDraftPlayersTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** 清除 updated_at 逾期(超過 1 天未更新)的草稿，連同綁定的大頭照媒體與實體檔案 */
    public function testPrunesExpiredDraftsWithMedia(): void
    {
        // GIVEN 一筆逾期未更新、已綁定大頭照的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $draft = DraftPlayer::query()->create([
            'email' => 'expired@example.com',
            'token' => 'secret',
        ]);
        $media = $draft->addMedia(UploadedFile::fake()->image('avatar.jpg'))
            ->toMediaCollection(CollectionName::PLAYER->value);
        $path = $media->getPathRelativeToRoot();
        DraftPlayer::query()->where('id', $draft->id)->update(['updated_at' => now()->subDays(2)]);

        Storage::disk('minio')->assertExists($path);

        // WHEN  執行清除 command
        $this->artisan('players:prune-draft')->assertSuccessful();

        // THEN  草稿、媒體記錄、實體檔案皆被刪除
        $this->assertDatabaseMissing('draft_players', ['id' => $draft->id]);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('minio')->assertMissing($path);
    }

    /** 未逾期(1 天內有更新)的草稿不受影響 */
    public function testKeepsUnexpiredDrafts(): void
    {
        // GIVEN 一筆剛更新過的草稿
        $draft = DraftPlayer::query()->create([
            'email' => 'fresh@example.com',
            'token' => 'secret',
        ]);

        // WHEN  執行清除 command
        $this->artisan('players:prune-draft')->assertSuccessful();

        // THEN  草稿仍存在
        $this->assertDatabaseHas('draft_players', ['id' => $draft->id]);
    }

    /** 恰好滿 1 天(updated_at 等於 now()-1day)：filter 是嚴格小於，尚未達門檻，不清除 */
    public function testKeepsDraftExactlyAtOneDayBoundary(): void
    {
        // GIVEN 凍結時間，草稿的 updated_at 恰好等於「現在 - 1 天」
        $frozenNow = now();
        Carbon::setTestNow($frozenNow);
        $draft = DraftPlayer::query()->create(['email' => 'boundary-kept@example.com', 'token' => 'secret']);
        DraftPlayer::query()->where('id', $draft->id)->update(['updated_at' => $frozenNow->copy()->subDay()]);

        // WHEN  執行清除 command
        $this->artisan('players:prune-draft')->assertSuccessful();

        // THEN  尚未嚴格小於門檻，草稿保留
        $this->assertDatabaseHas('draft_players', ['id' => $draft->id]);
    }

    /** 超過 1 天 1 秒：達門檻，清除 */
    public function testPrunesDraftJustOverOneDayBoundary(): void
    {
        // GIVEN 凍結時間，草稿的 updated_at 為「現在 - 1 天 - 1 秒」
        $frozenNow = now();
        Carbon::setTestNow($frozenNow);
        $draft = DraftPlayer::query()->create(['email' => 'boundary-pruned@example.com', 'token' => 'secret']);
        DraftPlayer::query()->where('id', $draft->id)->update(['updated_at' => $frozenNow->copy()->subDay()->subSecond()]);

        // WHEN  執行清除 command
        $this->artisan('players:prune-draft')->assertSuccessful();

        // THEN  已超過門檻，草稿被清除
        $this->assertDatabaseMissing('draft_players', ['id' => $draft->id]);
    }

    /** players:prune-draft 已以每日頻率註冊於排程 */
    public function testCommandIsScheduledDaily(): void
    {
        // GIVEN 應用程式排程
        $schedule = app(Schedule::class);

        // WHEN  搜尋 players:prune-draft 的排程事件
        $events = collect($schedule->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', 'players:prune-draft'));

        // THEN  恰有一筆，且為每日（0 0 * * *）
        $this->assertCount(1, $events);
        $this->assertSame('0 0 * * *', $events->first()->expression);
    }
}
