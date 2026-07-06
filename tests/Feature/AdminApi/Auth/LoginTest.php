<?php

namespace Tests\Feature\AdminApi\Auth;

use App\Enums\Admin\Status;
use App\Enums\ApiCode;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const RECAPTCHA_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** 帳號密碼正確且帳號啟用時，回傳 200 與 token */
    public function testLoginSuccessfully(): void
    {
        // GIVEN 一個 active 狀態的管理員，密碼為 secret123，reCAPTCHA 驗證會成功
        $this->fakeRecaptcha();

        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'status' => Status::ACTIVE,
        ]);

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 200 及非空的 token 字串
        $response->assertOk()
            ->assertJsonStructure(['data' => ['token']])
            ->assertJsonPath('data.token', fn ($v) => is_string($v) && strlen($v) > 0);
    }

    /** email 不存在時回傳 422 及 INVALID_CREDENTIALS apiCode */
    public function testLoginFailsWhenEmailNotFound(): void
    {
        // GIVEN 無任何管理員資料，reCAPTCHA 驗證會成功
        $this->fakeRecaptcha();

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，apiCode 為 INVALID_CREDENTIALS
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::INVALID_CREDENTIALS->value);
    }

    /** 密碼錯誤時回傳 422 及 INVALID_CREDENTIALS apiCode */
    public function testLoginFailsWhenPasswordWrong(): void
    {
        // GIVEN 一個管理員，密碼為 secret123，reCAPTCHA 驗證會成功
        $this->fakeRecaptcha();

        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        // WHEN  傳入錯誤密碼
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong_password',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，apiCode 為 INVALID_CREDENTIALS
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::INVALID_CREDENTIALS->value);
    }

    /** 帳號已停用時回傳 404 */
    public function testLoginFailsWhenAccountSuspended(): void
    {
        // GIVEN 一個 suspended 狀態的管理員，reCAPTCHA 驗證會成功
        $this->fakeRecaptcha();

        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'status' => Status::SUSPENDED,
        ]);

        // WHEN  以正確帳密登入
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 email 欄位時回傳 422 */
    public function testValidationFailsWhenEmailMissing(): void
    {
        // GIVEN 請求中無 email
        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，errors.email 含驗證錯誤
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** 缺少 password 欄位時回傳 422 */
    public function testValidationFailsWhenPasswordMissing(): void
    {
        // GIVEN 請求中無 password
        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，errors.password 含驗證錯誤
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /** email 格式不合法時回傳 422 */
    public function testValidationFailsWhenEmailInvalidFormat(): void
    {
        // GIVEN 傳入非 email 格式的字串
        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，errors.email 含驗證錯誤
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** 缺少 recaptcha_token 欄位時回傳 422 */
    public function testValidationFailsWhenRecaptchaTokenMissing(): void
    {
        // GIVEN 請求中無 recaptcha_token
        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        // THEN  回傳 422，errors.recaptcha_token 含驗證錯誤
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recaptcha_token']);
    }

    /** recaptcha_token 為空字串時回傳 422 */
    public function testValidationFailsWhenRecaptchaTokenEmpty(): void
    {
        // GIVEN recaptcha_token 為空字串
        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => '',
        ]);

        // THEN  回傳 422，errors.recaptcha_token 含驗證錯誤
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recaptcha_token']);
    }

    /** Google 回應 success=false 時回傳 422 及 RECAPTCHA_VERIFICATION_FAILED apiCode */
    public function testLoginFailsWhenRecaptchaSuccessIsFalse(): void
    {
        // GIVEN Google 回應驗證失敗，帳密皆正確
        $this->fakeRecaptcha(['success' => false]);

        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'status' => Status::ACTIVE,
        ]);

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，apiCode 為 RECAPTCHA_VERIFICATION_FAILED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::RECAPTCHA_VERIFICATION_FAILED->value);
    }

    /** Google 回應 score 低於門檻值時回傳 422 及 RECAPTCHA_VERIFICATION_FAILED apiCode */
    public function testLoginFailsWhenRecaptchaScoreBelowThreshold(): void
    {
        // GIVEN Google 回應 score 低於預設門檻 0.5
        $this->fakeRecaptcha(['score' => 0.1]);

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，apiCode 為 RECAPTCHA_VERIFICATION_FAILED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::RECAPTCHA_VERIFICATION_FAILED->value);
    }

    /** Google 回應 action 不符時回傳 422 及 RECAPTCHA_VERIFICATION_FAILED apiCode（防止其他表單的 token 被挪用到後台登入） */
    public function testLoginFailsWhenRecaptchaActionMismatch(): void
    {
        // GIVEN Google 回應的 action 為 player_login，非後台登入預期的 admin_login
        $this->fakeRecaptcha(['action' => 'player_login']);

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，apiCode 為 RECAPTCHA_VERIFICATION_FAILED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::RECAPTCHA_VERIFICATION_FAILED->value);
    }

    /** reCAPTCHA 驗證失敗時，即使帳密正確也不建立 token 且不回傳帳密驗證結果 */
    public function testLoginFailsWithRecaptchaErrorEvenWhenCredentialsCorrect(): void
    {
        // GIVEN reCAPTCHA 驗證失敗，但帳密實際上正確
        $this->fakeRecaptcha(['success' => false]);

        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'status' => Status::ACTIVE,
        ]);

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422 RECAPTCHA_VERIFICATION_FAILED（非 INVALID_CREDENTIALS），且未建立任何 token
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::RECAPTCHA_VERIFICATION_FAILED->value);
        $this->assertSame(0, $admin->tokens()->count());
    }

    /** 呼叫 Google reCAPTCHA API 連線失敗時回傳 422 及 RECAPTCHA_VERIFICATION_FAILED apiCode */
    public function testLoginFailsWhenRecaptchaApiConnectionFails(): void
    {
        // GIVEN 呼叫 Google siteverify 時連線失敗
        Http::fake(function (Request $request): void {
            throw new ConnectionException('Connection failed');
        });

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'recaptcha_token' => 'valid-token',
        ]);

        // THEN  回傳 422，apiCode 為 RECAPTCHA_VERIFICATION_FAILED
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::RECAPTCHA_VERIFICATION_FAILED->value);
    }

    /** 模擬 Google reCAPTCHA siteverify API 回應 */
    private function fakeRecaptcha(array $overrides = []): void
    {
        Http::fake([
            self::RECAPTCHA_URL => Http::response(array_merge([
                'success' => true,
                'score' => 0.9,
                'action' => 'admin_login',
            ], $overrides)),
        ]);
    }
}
