<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    | 有狀態網域（SPA Cookie 認證）
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    | 來自以下網域／主機的請求，將透過 Cookie 進行有狀態的 API 認證。
    | 通常需要填入透過前端 SPA 存取 API 的本機與正式環境網域。
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
        // Sanctum::currentRequestHost(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    | 認證守衛
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    | 此陣列定義 Sanctum 驗證請求時依序檢查的認證守衛。
    | 若所有守衛都無法驗證，Sanctum 會改用請求中的 Bearer Token 進行認證。
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    | Token 過期時間（分鐘）
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. This will override any values set in the token's
    | "expires_at" attribute, but first-party sessions are not affected.
    |
    | 控制 Token 的有效期限（單位：分鐘），超過此時間後 Token 將被視為已過期。
    | 此設定會覆蓋 Token 本身 "expires_at" 欄位的值，但不影響第一方 Session 的有效期。
    | 設為 null 表示 Token 永不過期。
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    | Token 前綴
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | security scanning initiatives maintained by open source platforms
    | that notify developers if they commit tokens into repositories.
    |
    | Sanctum 可為新產生的 Token 加上前綴，以便各大開源平台的安全掃描工具
    | 能自動偵測並通知開發者，避免 Token 不小心被提交到程式碼儲存庫中。
    |
    | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    | Sanctum 中介層
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    | 使用 Sanctum 為第一方 SPA 進行認證時，可在此自訂 Sanctum 處理請求所使用的中介層。
    | - authenticate_session：驗證 Session 是否有效
    | - encrypt_cookies：加密 Cookie 內容
    | - validate_csrf_token：驗證 CSRF Token，防止跨站請求偽造攻擊
    |
    */

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],
];
