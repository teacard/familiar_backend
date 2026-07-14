<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\ApiCode;
use App\Enums\DraftPlayer\Step;
use App\Models\DraftPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyCodeTest extends TestCase
{
    use RefreshDatabase;

    /** 尚未呼叫寄送驗證碼即嘗試驗證，回傳 VERIFICATION_CODE_NOT_SENT */
    public function testRejectsWhenCodeNeverSent(): void
    {
        // GIVEN 一筆從未寄送過驗證碼的草稿
        $token = $this->createDraftToken();

        // WHEN  直接呼叫驗證
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '123456']);

        // THEN  回傳 422，apiCode 為 VERIFICATION_CODE_NOT_SENT
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_NOT_SENT->value);
    }

    /** 驗證碼已過期，回傳 VERIFICATION_CODE_EXPIRED */
    public function testRejectsExpiredCode(): void
    {
        // GIVEN 驗證碼已過期的草稿
        $token = $this->createDraftToken();
        DraftPlayer::query()->update([
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->subMinute(),
        ]);

        // WHEN  嘗試驗證
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '123456']);

        // THEN  回傳 422，apiCode 為 VERIFICATION_CODE_EXPIRED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_EXPIRED->value);
    }

    /** 累計錯誤達 3 次即鎖定，回傳 VERIFICATION_CODE_LOCKED */
    public function testLocksAfterThreeFailedAttempts(): void
    {
        // GIVEN 有效驗證碼的草稿
        $token = $this->createDraftToken();
        DraftPlayer::query()->update([
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->addMinutes(5),
        ]);

        // WHEN  連續輸入錯誤驗證碼 3 次(每次皆為 VERIFICATION_CODE_INVALID，第 3 次後 attempts 達 3)
        for ($i = 0; $i < 3; ++$i) {
            $this->withHeader('Authorization', 'Bearer ' . $token)
                ->postJson('/api/registrations/verification-code/verify', ['code' => '000000'])
                ->assertUnprocessable()
                ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_INVALID->value);
        }

        // THEN  第 4 次呼叫(無論驗證碼是否正確)回傳 VERIFICATION_CODE_LOCKED
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '000000']);
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_LOCKED->value);

        // AND   即使此時輸入正確驗證碼，仍因鎖定被拒
        $lockedResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '123456']);
        $lockedResponse->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_LOCKED->value);
    }

    /** 驗證碼不符，回傳 VERIFICATION_CODE_INVALID 且錯誤次數 +1 */
    public function testRejectsInvalidCode(): void
    {
        // GIVEN 有效驗證碼的草稿
        $token = $this->createDraftToken();
        DraftPlayer::query()->update([
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->addMinutes(5),
        ]);

        // WHEN  輸入錯誤的驗證碼
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '999999']);

        // THEN  回傳 422，apiCode 為 VERIFICATION_CODE_INVALID，錯誤次數增加
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_INVALID->value);
        $this->assertSame(1, DraftPlayer::query()->first()->verification_attempts);
    }

    /** 驗證成功，registration_step 推進至第 2 步 */
    public function testAdvancesStepOnSuccess(): void
    {
        // GIVEN 有效驗證碼的草稿
        $token = $this->createDraftToken();
        DraftPlayer::query()->update([
            'verification_code' => '123456',
            'verification_code_expires_at' => now()->addMinutes(5),
        ]);

        // WHEN  輸入正確的驗證碼
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code/verify', ['code' => '123456']);

        // THEN  成功，registration_step 推進至 CODE_VERIFIED
        $response->assertOk();
        $this->assertSame(Step::CODE_VERIFIED, DraftPlayer::query()->first()->registration_step);
    }

    private function createDraftToken(string $email = 'verify-code@example.com'): string
    {
        $response = $this->postJson('/api/registrations', ['email' => $email]);

        return $response->json('data.token');
    }
}
