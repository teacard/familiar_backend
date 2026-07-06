<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Auth\LoginRequestData;
use App\Data\AdminApi\Response\Admin\ProfileResponse;
use App\Data\AdminApi\Response\Auth\LoginResponse;
use App\Enums\Auth\Guard;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Auth\LoginRequest;
use App\Models\Admin;
use App\Services\AuthService;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected RecaptchaService $recaptchaService,
    ) {
    }

    /** 後台登入 */
    public function login(LoginRequest $request): JsonResponse
    {
        $this->recaptchaService->verify(
            $request->recaptcha_token,
            $request->ip(),
            config('services.recaptcha.actions.admin_login'),
        );

        $admin = $this->authService->login(
            LoginRequestData::fromRequest($request)
        );

        $token = $admin->createToken('admin-api')->plainTextToken;

        return $this->success(new LoginResponse(token: $token));
    }

    /** 個人資料-取得 */
    public function profile(): JsonResponse
    {
        /** @var Admin $admin */
        $admin = auth(Guard::ADMIN->value)->user();

        // 僅需登入即可取得自身資料，不額外檢查權限
        return $this->success(
            ProfileResponse::fromModel($admin)
        );
    }
}
