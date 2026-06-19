<?php

namespace App\Http\Requests\TelescopeAuth;

use App\Enums\Admin\Status;
use App\Enums\Auth\ApiCode;
use App\Enums\Auth\Guard;
use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** 失敗上限 */
    private const MAX_ATTEMPTS = 5;

    /** 鎖定秒數 */
    private const DECAY_SECONDS = 60;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => trans('admin.attributes.adminapi.email'),
            'password' => trans('admin.attributes.adminapi.password'),
        ];
    }

    /**
     * 驗證帳密並登入 admin_web guard。
     * 僅 ACTIVE 且 is_super_admin 的 Admin 可通過；失敗以 IP 節流。
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (!Auth::guard(Guard::ADMIN_WEB->value)->attempt($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => trans('api-codes.' . ApiCode::INVALID_CREDENTIALS->value)]);
        }

        /** @var Admin $admin */
        $admin = Auth::guard(Guard::ADMIN_WEB->value)->user();

        if (Status::ACTIVE !== $admin->status || true !== $admin->is_super_admin) {
            Auth::guard(Guard::ADMIN_WEB->value)->logout();
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => trans('api-codes.' . ApiCode::TELESCOPE_ACCESS_FORBIDDEN->value)]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * 確認此 IP 未被節流鎖定。
     *
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        throw ValidationException::withMessages(['email' => trans('api-codes.' . ApiCode::TOO_MANY_LOGIN_ATTEMPTS->value, ['seconds' => RateLimiter::availableIn($this->throttleKey())])]);
    }

    /** 以用戶端 IP 為節流 key */
    private function throttleKey(): string
    {
        return 'telescope-login:' . $this->ip();
    }
}
