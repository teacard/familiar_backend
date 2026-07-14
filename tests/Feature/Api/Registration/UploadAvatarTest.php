<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\Media\CollectionName;
use App\Enums\TemporaryMedia\SystemName;
use App\Models\TemporaryMedia;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadAvatarTest extends TestCase
{
    use RefreshDatabase;

    /** 上傳大頭照成功，回傳 id+url，媒體存入 TEMPORARY 集合 */
    public function testUploadsAvatarToTemporaryCollection(): void
    {
        // GIVEN 已 seed 常駐 owner 的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $token = $this->createDraftToken();

        // WHEN  上傳大頭照
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/registrations/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        // THEN  回傳 200，含 id+url，媒體掛在 PLAYER owner 的 temporary 集合
        $response->assertOk()->assertJsonStructure(['data' => ['id', 'url']]);
        $owner = TemporaryMedia::query()->where('system_name', SystemName::PLAYER->value)->first();
        $this->assertCount(1, $owner->getMedia(CollectionName::TEMPORARY->value));
    }

    /** 未提供 avatar 檔案，回傳驗證錯誤，不建立任何媒體記錄 */
    public function testRejectsMissingAvatarFile(): void
    {
        // GIVEN 已 seed 常駐 owner 的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $token = $this->createDraftToken();

        // WHEN  未帶 avatar
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile/avatar');

        // THEN  回傳 422，errors.avatar，不建立媒體
        $response->assertUnprocessable()->assertJsonValidationErrors(['avatar']);
        $this->assertDatabaseCount('media', 0);
    }

    /** 不受 registration_step 限制，任何進度皆可呼叫（草稿剛建立、尚未驗證信箱） */
    public function testNotRestrictedByRegistrationStep(): void
    {
        // GIVEN 剛建立、僅完成 Step1 的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $token = $this->createDraftToken();

        // WHEN  在尚未驗證信箱的狀態下呼叫上傳大頭照
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/registrations/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        // THEN  仍然成功
        $response->assertOk();
    }

    private function createDraftToken(string $email = 'upload-avatar@example.com'): string
    {
        $response = $this->postJson('/api/registrations', ['email' => $email]);

        return $response->json('data.token');
    }
}
