<?php

namespace Tests\Feature\Media;

use App\Enums\Media\CollectionName;
use App\Enums\TemporaryMedia\SystemName;
use App\Models\Admin;
use App\Models\TemporaryMedia;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class PruneTemporaryMediaTest extends TestCase
{
    use RefreshDatabase;

    /** 清除超過 1 天的暫存媒體，連同實體檔案 */
    public function testPrunesTemporaryMediaOlderThanOneDay(): void
    {
        // GIVEN 一筆建立於 2 天前的暫存媒體
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $media = $this->addTemporaryMedia();
        $path = $media->getPathRelativeToRoot();
        $this->ageMedia($media, 2);

        Storage::disk('minio')->assertExists($path);

        // WHEN  執行清除 command
        $this->artisan('media:prune-temporary')->assertSuccessful();

        // THEN  媒體紀錄與實體檔案皆被刪除
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('minio')->assertMissing($path);
    }

    /** 保留 1 天內的暫存媒體 */
    public function testKeepsTemporaryMediaWithinOneDay(): void
    {
        // GIVEN 一筆剛建立的暫存媒體（在 1 天內）
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $media = $this->addTemporaryMedia();

        // WHEN  執行清除 command
        $this->artisan('media:prune-temporary')->assertSuccessful();

        // THEN  媒體仍存在
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    /** 只清除 temporary 集合，不影響其他集合（admin） */
    public function testDoesNotPruneOtherCollections(): void
    {
        // GIVEN 一筆建立於 2 天前、屬於 admin 集合的媒體
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $admin = Admin::factory()->create();
        $adminMedia = $admin->addMedia(UploadedFile::fake()->image('avatar.png'))
            ->toMediaCollection(CollectionName::ADMIN->value);
        $this->ageMedia($adminMedia, 2);

        // WHEN  執行清除 command
        $this->artisan('media:prune-temporary')->assertSuccessful();

        // THEN  admin 集合媒體不受影響
        $this->assertDatabaseHas('media', ['id' => $adminMedia->id]);
    }

    /** 清除後常駐 owner 列仍存在 */
    public function testKeepsTemporaryMediaOwner(): void
    {
        // GIVEN 一筆建立於 2 天前的暫存媒體
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $media = $this->addTemporaryMedia();
        $this->ageMedia($media, 2);

        // WHEN  執行清除 command
        $this->artisan('media:prune-temporary')->assertSuccessful();

        // THEN  暫存媒體被刪，但 owner 列仍存在
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseHas('temporary_media', ['system_name' => SystemName::ADMIN->value]);
    }

    /** media:prune-temporary 已以每日頻率註冊於排程 */
    public function testCommandIsScheduledDaily(): void
    {
        // GIVEN 應用程式排程
        $schedule = app(Schedule::class);

        // WHEN  搜尋 media:prune-temporary 的排程事件
        $events = collect($schedule->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', 'media:prune-temporary'));

        // THEN  恰有一筆，且為每日（0 0 * * *）
        $this->assertCount(1, $events);
        $this->assertSame('0 0 * * *', $events->first()->expression);
    }

    /** 在 admin 常駐 owner 的 temporary 集合加入一筆媒體 */
    private function addTemporaryMedia(): Media
    {
        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();

        return $owner->addMedia(UploadedFile::fake()->image('temp.png'))
            ->toMediaCollection(CollectionName::TEMPORARY->value);
    }

    /** 將媒體的 created_at 往前調整指定天數（繞過 model touch） */
    private function ageMedia(Media $media, int $days): void
    {
        Media::query()->where('id', $media->id)->update(['created_at' => now()->subDays($days)]);
    }
}
