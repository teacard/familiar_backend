<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\ApiCode;
use App\Enums\DraftPlayer\Step;
use App\Models\DraftPlayer;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StepOrderTest extends TestCase
{
    use RefreshDatabase;

    /** 較前的狀態呼叫較後步驟端點(Step3-B)被拒絕：跳步 */
    public function testRejectsSkippingToUpdateProfile(): void
    {
        // GIVEN 僅完成 Step1(尚未驗證信箱)的草稿，但已有一筆合法的 mediaId 可供送出(避免先撞到 FormRequest 的 exists 驗證)
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $token = $this->createDraftToken(Step::EMAIL_SUBMITTED);
        $mediaId = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/registrations/profile/avatar', ['avatar' => UploadedFile::fake()->image('avatar.jpg')])
            ->json('data.id');

        // WHEN  直接呼叫送出表單(Step3-B)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', ['name' => '生物蒐集家', 'mediaId' => $mediaId]);

        // THEN  回傳 422，apiCode 為 REGISTRATION_STEP_SKIPPED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_STEP_SKIPPED->value);
    }

    /** 較前的狀態呼叫較後步驟端點(Step4)被拒絕：跳步 */
    public function testRejectsSkippingToComplete(): void
    {
        // GIVEN 僅完成 Step1 的草稿
        $token = $this->createDraftToken(Step::EMAIL_SUBMITTED);

        // WHEN  直接呼叫完成註冊(Step4)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/password', ['password' => 'password123', 'passwordConfirmation' => 'password123']);

        // THEN  回傳 422，apiCode 為 REGISTRATION_STEP_SKIPPED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_STEP_SKIPPED->value);
    }

    /** 較後狀態重新呼叫較前步驟端點(Step2-A)被允許 */
    public function testAllowsCallingEarlierSendVerificationCodeAfterProgressing(): void
    {
        // GIVEN 已完成 Step3(暱稱與大頭照)的草稿
        $token = $this->createDraftToken(Step::PROFILE_COMPLETED);

        // WHEN  重新呼叫 Step2-A(寄送驗證碼)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code');

        // THEN  允許處理，不因「已超前」而拒絕
        $response->assertOk();
    }

    /** 較後狀態重新呼叫較前步驟端點(Step2-B)被允許 */
    public function testAllowsCallingEarlierVerifyCodeAfterProgressing(): void
    {
        // GIVEN 已完成 Step3 的草稿，且已重新寄送一組驗證碼
        $token = $this->createDraftToken(Step::PROFILE_COMPLETED);
        DraftPlayer::query()->update([
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->addMinutes(5),
        ]);

        // WHEN  重新呼叫 Step2-B(驗證驗證碼)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '123456']);

        // THEN  允許處理，registration_step 不因此倒退
        $response->assertOk();
        $this->assertSame(Step::PROFILE_COMPLETED, DraftPlayer::query()->first()->registration_step);
    }

    /** Step3-A(上傳大頭照)不受 registration_step 限制，剛建立(第 1 步)的草稿也可呼叫 */
    public function testUploadAvatarNotRestrictedByStep(): void
    {
        // GIVEN 僅完成 Step1 的草稿
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $token = $this->createDraftToken(Step::EMAIL_SUBMITTED);

        // WHEN  呼叫上傳大頭照
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/registrations/profile/avatar', ['avatar' => UploadedFile::fake()->image('avatar.jpg')]);

        // THEN  成功，不受進度限制
        $response->assertOk();
    }

    private function createDraftToken(Step $step, string $email = 'step-order@example.com'): string
    {
        $response = $this->postJson('/api/registrations', ['email' => $email]);
        $token = $response->json('data.token');

        DraftPlayer::query()->update(['registration_step' => $step]);

        return $token;
    }
}
