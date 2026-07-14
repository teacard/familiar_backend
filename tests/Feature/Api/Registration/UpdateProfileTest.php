<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\ApiCode;
use App\Enums\DraftPlayer\Step;
use App\Enums\Media\CollectionName;
use App\Models\DraftPlayer;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    /** 送出暱稱與 mediaId 成功，媒體從 TEMPORARY 轉綁至草稿，registration_step 推進至第 3 步 */
    public function testUpdatesProfileAndBindsMedia(): void
    {
        // GIVEN 已驗證信箱、已上傳大頭照的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $token = $this->createVerifiedDraftToken();
        $mediaId = $this->uploadAvatar($token);

        // WHEN  送出暱稱與 mediaId
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '生物蒐集家', 'mediaId' => $mediaId]);

        // THEN  成功，草稿的 name、registration_step 更新，媒體轉綁至草稿的 PLAYER 集合
        $response->assertOk();
        $draft = DraftPlayer::query()->first();
        $this->assertSame('生物蒐集家', $draft->name);
        $this->assertSame(Step::PROFILE_COMPLETED, $draft->registration_step);
        $this->assertSame($mediaId, $draft->getFirstMedia(CollectionName::PLAYER->value)->id);
    }

    /** 重複上傳大頭照兩次取得不同 mediaId，用最後一次送出後草稿只綁定該張圖，舊圖媒體與實體檔案一併移除 */
    public function testReuploadingAvatarReplacesOldMediaWithoutOrphans(): void
    {
        // GIVEN 已上傳過一張大頭照並送出表單的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $token = $this->createVerifiedDraftToken();
        $firstMediaId = $this->uploadAvatar($token);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '第一次', 'mediaId' => $firstMediaId])
            ->assertOk();

        // WHEN  重新上傳第二張圖並再次送出表單
        $secondMediaId = $this->uploadAvatar($token);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '第二次', 'mediaId' => $secondMediaId]);

        // THEN  成功，草稿只綁定第二張圖，第一張圖的媒體記錄已被刪除(不產生孤兒媒體)
        $response->assertOk();
        $draft = DraftPlayer::query()->first();
        $this->assertCount(1, $draft->getMedia(CollectionName::PLAYER->value));
        $this->assertSame($secondMediaId, $draft->getFirstMedia(CollectionName::PLAYER->value)->id);
        $this->assertDatabaseMissing('media', ['id' => $firstMediaId]);
    }

    /** 未提供 mediaId，回傳驗證錯誤，不更新草稿 name 與 registration_step */
    public function testRejectsMissingMediaId(): void
    {
        // GIVEN 已驗證信箱的草稿
        $token = $this->createVerifiedDraftToken();

        // WHEN  送出時未帶 mediaId
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '生物蒐集家']);

        // THEN  回傳 422，errors.mediaId，草稿未更新
        $response->assertUnprocessable()->assertJsonValidationErrors(['mediaId']);
        $draft = DraftPlayer::query()->first();
        $this->assertNull($draft->name);
        $this->assertSame(Step::CODE_VERIFIED, $draft->registration_step);
    }

    /** 提供的 mediaId 查無對應媒體，回傳驗證錯誤，不更新草稿 */
    public function testRejectsNonExistentMediaId(): void
    {
        // GIVEN 已驗證信箱的草稿
        $token = $this->createVerifiedDraftToken();

        // WHEN  送出不存在的 mediaId
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '生物蒐集家', 'mediaId' => 999999]);

        // THEN  回傳 422，errors.mediaId，草稿未更新
        $response->assertUnprocessable()->assertJsonValidationErrors(['mediaId']);
        $this->assertNull(DraftPlayer::query()->first()->name);
    }

    /** registration_step 未達第 2 步(尚未驗證信箱)即呼叫，回傳 REGISTRATION_STEP_SKIPPED */
    public function testRejectsWhenStepSkipped(): void
    {
        // GIVEN 僅完成 Step1、尚未驗證信箱的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $response = $this->postJson('/api/registrations', ['email' => 'skip-step@example.com']);
        $token = $response->json('data.token');
        $mediaId = $this->uploadAvatar($token);

        // WHEN  跳過驗證信箱直接送出表單
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '生物蒐集家', 'mediaId' => $mediaId]);

        // THEN  回傳 422，apiCode 為 REGISTRATION_STEP_SKIPPED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_STEP_SKIPPED->value);
    }

    private function createVerifiedDraftToken(string $email = 'update-profile@example.com'): string
    {
        $response = $this->postJson('/api/registrations', ['email' => $email]);
        $token = $response->json('data.token');

        DraftPlayer::query()->update([
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->addMinutes(5),
        ]);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '123456'])
            ->assertOk();

        return $token;
    }

    private function uploadAvatar(string $token): int
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/registrations/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        return $response->json('data.id');
    }
}
