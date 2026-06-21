<?php

namespace Tests\Feature\Media;

use App\Enums\Media\CollectionName;
use App\Enums\TemporaryMedia\SystemName;
use App\Models\Admin;
use App\Models\TemporaryMedia;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    /** 已登入後台人員上傳 jpg 成功：回 200、id+url，media 進 admin owner 的 temporary 集合 */
    public function testUploadsJpgToTemporaryCollection(): void
    {
        // GIVEN 已 seed 常駐 owner 的已登入後台人員，媒體 disk 以 fake 模擬
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  上傳 jpg 圖片
        $response = $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        // THEN  回 200，含 id 與 url；media 掛在 admin owner 的 temporary 集合
        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'url']]);

        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();
        $this->assertCount(1, $owner->getMedia(CollectionName::TEMPORARY->value));
    }

    /** 上傳 png 同樣成功 */
    public function testUploadsPng(): void
    {
        // GIVEN 已 seed 常駐 owner 的已登入後台人員
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  上傳 png
        $response = $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->image('photo.png'),
            ]);

        // THEN  回 200
        $response->assertOk();
    }

    /** 命名以 {type}_ 為前綴並保留副檔名，自訂屬性含 type 與原始檔名 */
    public function testStoresNamingAndCustomProperties(): void
    {
        // GIVEN 已 seed 常駐 owner 的已登入後台人員
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  上傳檔名為 photo.png 的圖片
        $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->image('photo.png'),
            ])->assertOk();

        // THEN  file_name 前綴 image_、保留 .png；自訂屬性含 type 與原始檔名
        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();
        $media = $owner->getFirstMedia(CollectionName::TEMPORARY->value);

        $this->assertStringStartsWith('image_', $media->file_name);
        $this->assertStringEndsWith('.png', $media->file_name);
        $this->assertSame('image', $media->getCustomProperty('type'));
        $this->assertSame('photo.png', $media->getCustomProperty('original_file_name'));
    }

    /** 暫存媒體掛在 admin 常駐 owner，而非登入人員 */
    public function testMediaOwnerIsTemporaryMediaNotAdmin(): void
    {
        // GIVEN 已 seed 常駐 owner 的已登入後台人員
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  上傳圖片
        $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->image('photo.png'),
            ])->assertOk();

        // THEN  media 的 owner 為 TemporaryMedia，且登入者本人未掛任何 media
        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();
        $this->assertDatabaseHas('media', [
            'model_type' => $owner->getMorphClass(),
            'model_id' => $owner->id,
            'collection_name' => CollectionName::TEMPORARY->value,
        ]);
    }

    /** 未登入回 401，且不建立任何 media */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 已 seed 常駐 owner，但未帶 Token
        $this->seed(TemporaryMediaSeeder::class);

        // WHEN  未登入上傳（auth 在驗證前短路，故以 JSON 請求即可測得 401）
        $response = $this->postJson('/admin-api/media');

        // THEN  回 401，無 media 建立
        $response->assertUnauthorized();
        $this->assertDatabaseCount('media', 0);
    }

    /** 缺 file 回 422 */
    public function testRejectsMissingFile(): void
    {
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  未帶 file
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/media');

        // THEN  回 422，errors.file
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /** 非 jpg/png（gif）回 422 */
    public function testRejectsGif(): void
    {
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  上傳 gif
        $response = $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->image('photo.gif'),
            ]);

        // THEN  回 422，errors.file
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /** 非圖片檔（pdf）回 422 */
    public function testRejectsPdf(): void
    {
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  上傳 pdf
        $response = $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            ]);

        // THEN  回 422，errors.file
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /** 檔案超過 10MB 回 422 */
    public function testRejectsOversizeFile(): void
    {
        $this->seed(TemporaryMediaSeeder::class);
        $actor = Admin::factory()->create();

        // WHEN  上傳 10241KB（>10MB）的圖片
        $response = $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->image('big.jpg')->size(10241),
            ]);

        // THEN  回 422，errors.file
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    /** 常駐 owner 未 seed 時上傳：回 404，且不建立 media */
    public function testReturns404WhenOwnerNotSeeded(): void
    {
        // GIVEN 未 seed 常駐 owner 的已登入後台人員
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = Admin::factory()->create();

        // WHEN  上傳圖片
        $response = $this->actingAs($actor, 'admin')
            ->post('/admin-api/media', [
                'file' => UploadedFile::fake()->image('photo.png'),
            ]);

        // THEN  回 404，無 media 建立
        $response->assertNotFound();
        $this->assertDatabaseCount('media', 0);
    }
}
