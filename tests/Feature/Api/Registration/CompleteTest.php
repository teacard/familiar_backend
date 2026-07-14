<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\ApiCode;
use App\Models\DraftPlayer;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompleteTest extends TestCase
{
    use RefreshDatabase;

    /** 密碼少於 8 碼，回傳驗證錯誤且不建立正式玩家 */
    public function testRejectsShortPassword(): void
    {
        // GIVEN 已完成 Step3 的草稿
        $token = $this->createProfileCompletedDraftToken();

        // WHEN  密碼只有 7 碼
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/password', [
                'password' => '1234567',
                'passwordConfirmation' => '1234567',
            ]);

        // THEN  回傳 422，errors.password，不建立正式玩家
        $response->assertUnprocessable()->assertJsonValidationErrors(['password']);
        $this->assertDatabaseCount('players', 0);
    }

    /** 密碼與確認密碼不一致，回傳驗證錯誤且不建立正式玩家 */
    public function testRejectsMismatchedPasswords(): void
    {
        // GIVEN 已完成 Step3 的草稿
        $token = $this->createProfileCompletedDraftToken();

        // WHEN  password 與 passwordConfirmation 不同
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/password', [
                'password' => 'password123',
                'passwordConfirmation' => 'different123',
            ]);

        // THEN  回傳 422，errors.password，不建立正式玩家
        $response->assertUnprocessable()->assertJsonValidationErrors(['password']);
        $this->assertDatabaseCount('players', 0);
    }

    /** registration_step 未達第 3 步即呼叫，回傳 REGISTRATION_STEP_SKIPPED */
    public function testRejectsWhenStepSkipped(): void
    {
        // GIVEN 僅完成 Step1 的草稿(尚未驗證信箱、尚未填寫暱稱與大頭照)
        $response = $this->postJson('/api/registrations', ['email' => 'skip-complete@example.com']);
        $token = $response->json('data.token');

        // WHEN  直接呼叫完成註冊
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/password', [
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 422，apiCode 為 REGISTRATION_STEP_SKIPPED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_STEP_SKIPPED->value);
    }

    private function createProfileCompletedDraftToken(string $email = 'complete@example.com'): string
    {
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);

        $response = $this->postJson('/api/registrations', ['email' => $email]);
        $token = $response->json('data.token');

        DraftPlayer::query()->update([
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->addMinutes(5),
        ]);
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '123456'])
            ->assertOk();

        $mediaResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/registrations/profile/avatar', ['avatar' => UploadedFile::fake()->image('avatar.jpg')]);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/profile', [
                'name' => '生物蒐集家',
                'mediaId' => $mediaResponse->json('data.id'),
            ])
            ->assertOk();

        return $token;
    }
}
