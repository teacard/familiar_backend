<?php

namespace Tests\Feature\Media;

use App\Enums\Media\CollectionName;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Spatie\MediaLibrary\HasMedia;
use Tests\TestCase;

class AdminAvatarTest extends TestCase
{
    use RefreshDatabase;

    /** avatar 為單檔：加入第二張後僅保留最新一張 */
    public function testAvatarIsSingleFile(): void
    {
        // GIVEN 一個 admin（媒體 disk 以 fake 模擬）
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $admin = Admin::factory()->create();

        // WHEN  連續加入兩張 avatar
        $admin->addMedia(UploadedFile::fake()->create('first.png', 10, 'image/png'))
            ->toMediaCollection(CollectionName::ADMIN->value);
        $admin->addMedia(UploadedFile::fake()->create('second.png', 10, 'image/png'))
            ->toMediaCollection(CollectionName::ADMIN->value);

        // THEN  admin 集合僅剩一張，且為最新的 second.png
        $media = $admin->getMedia(CollectionName::ADMIN->value);
        $this->assertCount(1, $media);
        $this->assertSame('second.png', $media->first()->file_name);
    }

    /** Admin 實作 HasMedia；頭像為選填，無頭像時 media 層不回 fallback（由 API response 處理） */
    public function testAdminImplementsHasMediaWithOptionalAvatar(): void
    {
        // GIVEN 一個沒有任何頭像的 admin
        $admin = Admin::factory()->create();

        // THEN  Admin 為 HasMedia；admin 集合無媒體，且 media 層不掛 fallback（回空字串）
        $this->assertInstanceOf(HasMedia::class, $admin);
        $this->assertCount(0, $admin->getMedia(CollectionName::ADMIN->value));
        $this->assertSame('', $admin->getFirstMediaUrl(CollectionName::ADMIN->value));
    }

    /** media 資料表已建立 */
    public function testMediaTableExists(): void
    {
        // THEN  media 表存在
        $this->assertTrue(Schema::hasTable('media'));
    }

    /** medialibrary 預設 disk 解析為 minio */
    public function testMediaDiskNameResolvesToMinio(): void
    {
        // THEN  config('media-library.disk_name') 為 minio
        $this->assertSame('minio', config('media-library.disk_name'));
    }

    /** S3 flysystem driver 已安裝 */
    public function testS3FlysystemDriverInstalled(): void
    {
        // THEN  AwsS3V3Adapter 類別存在
        $this->assertTrue(class_exists(AwsS3V3Adapter::class));
    }
}
