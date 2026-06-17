<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Auth\LoginRequestData;
use App\Data\AdminApi\Response\Auth\LoginResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    /** 後台登入 */
    public function login(LoginRequest $request): JsonResponse
    {
        $admin = $this->authService->login(
            LoginRequestData::fromRequest($request)
        );

        $token = $admin->createToken('admin-api')->plainTextToken;

        return $this->success(new LoginResponse(token: $token));
    }
}
