<?php

namespace Tests\Feature\AdminApi\Auth;

use App\Enums\Admin\Status;
use App\Enums\Auth\ApiCode;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** 帳號密碼正確且帳號啟用時，回傳 200 與 token */
    public function testLoginSuccessfully(): void
    {
        // GIVEN 一個 active 狀態的管理員，密碼為 secret123
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'status' => Status::ACTIVE,
        ]);

        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        // THEN  回傳 200 及非空的 token 字串
        $response->assertOk()
            ->assertJsonStructure(['data' => ['token']])
            ->assertJsonPath('data.token', fn ($v) => is_string($v) && strlen($v) > 0);
    }

    /** email 不存在時回傳 422 及 INVALID_CREDENTIALS apiCode */
    public function testLoginFailsWhenEmailNotFound(): void
    {
        // GIVEN 無任何管理員資料
        // WHEN  POST /admin-api/auth/login
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'secret123',
        ]);

        // THEN  回傳 422，apiCode 為 INVALID_CREDENTIALS
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::INVALID_CREDENTIALS->value);
    }

    /** 密碼錯誤時回傳 422 及 INVALID_CREDENTIALS apiCode */
    public function testLoginFailsWhenPasswordWrong(): void
    {
        // GIVEN 一個管理員，密碼為 secret123
        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        // WHEN  傳入錯誤密碼
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong_password',
        ]);

        // THEN  回傳 422，apiCode 為 INVALID_CREDENTIALS
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::INVALID_CREDENTIALS->value);
    }

    /** 帳號已停用時回傳 404 */
    public function testLoginFailsWhenAccountSuspended(): void
    {
        // GIVEN 一個 suspended 狀態的管理員
        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'status' => Status::SUSPENDED,
        ]);

        // WHEN  以正確帳密登入
        $response = $this->postJson('/admin-api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
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
        ]);

        // THEN  回傳 422，errors.email 含驗證錯誤
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
