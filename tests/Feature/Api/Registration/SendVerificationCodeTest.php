<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\ApiCode;
use App\Mail\PlayerRegistrationVerificationCode;
use App\Models\DraftPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendVerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** 60 秒內重複呼叫觸發重寄冷卻錯誤，verification_code 不變 */
    public function testRejectsResendWithinCooldown(): void
    {
        // GIVEN 已寄送過一次驗證碼的草稿
        Mail::fake();
        $token = $this->createDraftToken();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code')
            ->assertOk();
        $codeAfterFirstSend = DraftPlayer::query()->first()->verification_code;

        // WHEN  60 秒內再次呼叫
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code');

        // THEN  回傳 422，apiCode 為 VERIFICATION_CODE_RESEND_TOO_SOON，驗證碼未變
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_RESEND_TOO_SOON->value);
        $this->assertSame($codeAfterFirstSend, DraftPlayer::query()->first()->verification_code);
    }

    /** 超過 60 秒冷卻後可再次寄送 */
    public function testAllowsResendAfterCooldown(): void
    {
        // GIVEN 已寄送過一次驗證碼，且距今已超過 60 秒
        Mail::fake();
        $token = $this->createDraftToken();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code')
            ->assertOk();

        DraftPlayer::query()->update([
            'verification_code_expires_at' => now()->addMinutes(5)->subSeconds(61),
        ]);

        // WHEN  再次呼叫寄送
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code');

        // THEN  成功寄送
        $response->assertOk();
        Mail::assertQueued(PlayerRegistrationVerificationCode::class, 2);
    }

    /** 剛好還差 1 秒滿 60 秒冷卻(已過 59 秒)：仍在冷卻中，拒絕重寄 */
    public function testRejectsResendAtFiftyNineSeconds(): void
    {
        // GIVEN 凍結時間，草稿的驗證碼是「59 秒前」寄出的(距離 60 秒冷卻尚差 1 秒)
        Carbon::setTestNow(now());
        Mail::fake();
        $token = $this->createDraftToken();
        DraftPlayer::query()->update([
            'verification_code_expires_at' => now()->addMinutes(5)->subSeconds(59),
        ]);

        // WHEN  呼叫寄送
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code');

        // THEN  仍在冷卻中，回傳 422
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::VERIFICATION_CODE_RESEND_TOO_SOON->value);
    }

    /** 剛好滿 60 秒冷卻：允許重寄 */
    public function testAllowsResendAtExactlySixtySeconds(): void
    {
        // GIVEN 凍結時間，草稿的驗證碼是「恰好 60 秒前」寄出的
        Carbon::setTestNow(now());
        Mail::fake();
        $token = $this->createDraftToken();
        DraftPlayer::query()->update([
            'verification_code_expires_at' => now()->addMinutes(5)->subSeconds(60),
        ]);

        // WHEN  呼叫寄送
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code');

        // THEN  冷卻已滿，允許重寄
        $response->assertOk();
    }

    /** 同 IP 超過 throttle 門檻（每分鐘 6 次）時拒絕請求，且不寄信、不消耗驗證碼欄位 */
    public function testRejectsWhenIpThrottleExceeded(): void
    {
        // GIVEN 7 個不同草稿的 token（避免撞到 60 秒重寄冷卻，改用不同草稿測 IP 層級限制）
        Mail::fake();
        $tokens = collect(range(1, 7))->map(fn ($i) => $this->createDraftToken("throttle{$i}@example.com"));

        // WHEN  同一 IP 連續呼叫 7 次(超過每分鐘 6 次上限)
        $responses = $tokens->map(fn ($token) => $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code'));

        // THEN  前 6 次成功，第 7 次被 429 拒絕
        for ($i = 0; $i < 6; ++$i) {
            $responses[$i]->assertOk();
        }
        $responses[6]->assertStatus(429);

        // AND   恰好只寄出 6 封信(第 7 筆被拒的草稿沒有寄信)，第 7 筆草稿的 verification_code 仍是 null
        Mail::assertQueued(PlayerRegistrationVerificationCode::class, 6);
        $rejectedDraft = DraftPlayer::query()->where('email', 'throttle7@example.com')->first();
        $this->assertNull($rejectedDraft->verification_code);
    }

    /** 不要求 reCAPTCHA token，未帶此欄位也應成功 */
    public function testSucceedsWithoutRecaptchaToken(): void
    {
        // GIVEN 一筆草稿
        Mail::fake();
        $token = $this->createDraftToken();

        // WHEN  呼叫寄送(不帶任何 recaptcha 相關欄位)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/registrations/verification-code', []);

        // THEN  成功
        $response->assertOk();
    }

    private function createDraftToken(string $email = 'send-code@example.com'): string
    {
        $response = $this->postJson('/api/registrations', ['email' => $email]);

        return $response->json('data.token');
    }
}
