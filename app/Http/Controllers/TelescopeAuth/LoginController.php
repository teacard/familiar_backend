<?php

namespace App\Http\Controllers\TelescopeAuth;

use App\Enums\Admin\Status;
use App\Enums\Auth\Guard;
use App\Http\Controllers\Controller;
use App\Http\Requests\TelescopeAuth\LoginRequest;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Telescope 後台 web session 登入。
 * 僅 ACTIVE 且 is_super_admin 的 Admin 可登入；驗證與節流邏輯內聚於 LoginRequest。
 */
class LoginController extends Controller
{
    /** 顯示登入頁 */
    public function showLoginForm(): View|RedirectResponse
    {
        $admin = Auth::guard(Guard::ADMIN_WEB->value)->user();
        // 已是合格超級管理員則直接進 Telescope
        if ($admin instanceof Admin && Status::ACTIVE === $admin->status && true === $admin->is_super_admin) {
            return redirect('/' . config('telescope.path'));
        }

        return view('telescope-auth.login');
    }

    /** 處理登入 */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect('/' . config('telescope.path'));
    }
}
