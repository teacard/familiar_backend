<?php

namespace Tests\Feature\Api\Registration;

use App\Enums\ApiCode;
use App\Models\DraftPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenGuardTest extends TestCase
{
    use RefreshDatabase;

    /** 完全未帶 Authorization header，回傳 422，非 500 */
    public function testRejectsMissingToken(): void
    {
        // WHEN  完全不帶 Authorization header
        $response = $this->getJson('/api/registrations');

        // THEN  回傳 422，apiCode 為 REGISTRATION_DRAFT_NOT_FOUND
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value);
    }

    /** token 不含 `|` 分隔符，回傳 422，非 500 */
    public function testRejectsTokenWithoutSeparator(): void
    {
        // WHEN  token 不含 `|`
        $response = $this->withHeader('Authorization', 'Bearer abcdefgh')
            ->getJson('/api/registrations');

        // THEN  回傳 422，apiCode 為 REGISTRATION_DRAFT_NOT_FOUND
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value);
    }

    /** draftId 部分非純數字，回傳 422，非 500 */
    public function testRejectsNonNumericDraftId(): void
    {
        // WHEN  draftId 部分不是數字
        $response = $this->withHeader('Authorization', 'Bearer abc|secret')
            ->getJson('/api/registrations');

        // THEN  回傳 422，apiCode 為 REGISTRATION_DRAFT_NOT_FOUND
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value);
    }

    /** 查無對應草稿的 draftId，回傳 422，非 500 */
    public function testRejectsNonExistentDraftId(): void
    {
        // WHEN  draftId 查無對應草稿
        $response = $this->withHeader('Authorization', 'Bearer 999999|secret')
            ->getJson('/api/registrations');

        // THEN  回傳 422，apiCode 為 REGISTRATION_DRAFT_NOT_FOUND
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value);
    }

    /** secret 不符，回傳 422，非 500 */
    public function testRejectsWrongSecret(): void
    {
        // GIVEN 一筆存在的草稿
        $draft = DraftPlayer::query()->create([
            'email' => 'wrong-secret@example.com',
            'token' => 'correct-secret',
        ]);

        // WHEN  帶入正確 draftId 但錯誤的 secret
        $response = $this->withHeader('Authorization', 'Bearer ' . $draft->id . '|wrong-secret')
            ->getJson('/api/registrations');

        // THEN  回傳 422，apiCode 為 REGISTRATION_DRAFT_NOT_FOUND
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value);
    }

    /** 同樣的錯誤 token 防護也套用在其他寫入端點(非僅 GET) */
    public function testInvalidTokenIsRejectedOnWriteEndpointsToo(): void
    {
        // WHEN  以不合法 token 呼叫寄送驗證碼與完成註冊
        $sendResponse = $this->withHeader('Authorization', 'Bearer 999999|wrong')
            ->postJson('/api/registrations/verification-code');
        $completeResponse = $this->withHeader('Authorization', 'Bearer 999999|wrong')
            ->postJson('/api/registrations/password', ['password' => 'password123', 'passwordConfirmation' => 'password123']);

        // THEN  皆回傳 422，apiCode 為 REGISTRATION_DRAFT_NOT_FOUND
        $sendResponse->assertUnprocessable()->assertJsonPath('apiCode', ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value);
        $completeResponse->assertUnprocessable()->assertJsonPath('apiCode', ApiCode::REGISTRATION_DRAFT_NOT_FOUND->value);
    }
}
