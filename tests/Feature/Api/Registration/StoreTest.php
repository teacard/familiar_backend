<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\ApiCode;
use App\Enums\DraftPlayer\Step;
use App\Models\DraftPlayer;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** 全新 email 建立草稿，回傳一次性 token，不寄送任何驗證碼信件 */
    public function testCreatesDraftAndReturnsToken(): void
    {
        // GIVEN 全新的 email
        Mail::fake();

        // WHEN  提交 Step1
        $response = $this->postJson('/api/registrations', ['email' => 'new@example.com']);

        // THEN  回傳 200 與 token，建立 registration_step 為第 1 步的草稿，未寄信
        $response->assertOk()->assertJsonStructure(['data' => ['token']]);
        $this->assertDatabaseHas('draft_players', [
            'email' => 'new@example.com',
            'registration_step' => Step::EMAIL_SUBMITTED->value,
        ]);
        Mail::assertNothingSent();
    }

    /** email 已是正式會員時回傳 EMAIL_ALREADY_REGISTERED，不建立草稿 */
    public function testRejectsAlreadyRegisteredEmail(): void
    {
        // GIVEN 該 email 已是正式會員
        Player::factory()->create(['email' => 'existing@example.com']);

        // WHEN  提交 Step1
        $response = $this->postJson('/api/registrations', ['email' => 'existing@example.com']);

        // THEN  回傳 422，apiCode 為 EMAIL_ALREADY_REGISTERED，不建立草稿
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::EMAIL_ALREADY_REGISTERED->value);
        $this->assertDatabaseMissing('draft_players', ['email' => 'existing@example.com']);
    }

    /** 中斷後以同一 email 重新提交：延續既有進度與驗證碼，不歸零、不重寄，但換發新 token 使舊 token 失效 */
    public function testResumesExistingDraftWithNewToken(): void
    {
        // GIVEN 已完成 Step1 並驗證過驗證碼的草稿（registration_step 已推進至第 2 步）
        $firstResponse = $this->postJson('/api/registrations', ['email' => 'resume@example.com']);
        $oldToken = $firstResponse->json('data.token');

        $draft = DraftPlayer::query()->where('email', 'resume@example.com')->first();
        $draft->update([
            'verification_code' => '654321',
            'verification_code_expires_at' => now()->addMinutes(5),
            'registration_step' => Step::CODE_VERIFIED,
        ]);

        // WHEN  以同一 email 重新提交 Step1
        $secondResponse = $this->postJson('/api/registrations', ['email' => 'resume@example.com']);
        $newToken = $secondResponse->json('data.token');

        // THEN  回傳新 token（與舊 token 不同），registration_step 與 verification_code 維持不變
        $secondResponse->assertOk();
        $this->assertNotSame($oldToken, $newToken);
        $draft->refresh();
        $this->assertSame(Step::CODE_VERIFIED, $draft->registration_step);
        $this->assertSame('654321', $draft->verification_code);
        $this->assertDatabaseCount('draft_players', 1);

        // AND   舊 token 已失效，新 token 可正常存取
        $this->withHeader('Authorization', 'Bearer ' . $oldToken)
            ->getJson('/api/registrations')
            ->assertUnprocessable();
        $this->withHeader('Authorization', 'Bearer ' . $newToken)
            ->getJson('/api/registrations')
            ->assertOk();
    }

    /** Step1 不受任何頻率限制，可重複呼叫 */
    public function testNotRateLimited(): void
    {
        // WHEN  連續提交 10 次(不同 email)
        for ($i = 0; $i < 10; ++$i) {
            $response = $this->postJson('/api/registrations', ['email' => "burst{$i}@example.com"]);

            // THEN  每次皆成功，不因頻率被拒
            $response->assertOk();
        }
    }
}
