<?php

namespace App\Http\Middleware;

use App\Enums\Admin\Status;
use App\Enums\Auth\Guard;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 守護 Telescope 路由：僅允許以 admin_web session 登入、狀態 ACTIVE 且為超級管理員的 Admin。
 * 未登入者導向登入頁；已登入但不符資格者登出後導向登入頁。
 */
class EnsureTelescopeSuperAdmin
{
    public function handle(Request $request, \Closure $next): Response
    {
        $guard = Auth::guard(Guard::ADMIN_WEB->value);

        /** @var Admin|null $admin */
        $admin = $guard->user();

        if (!$admin instanceof Admin || Status::ACTIVE !== $admin->status || true !== $admin->is_super_admin) {
            $guard->logout();

            return redirect()->route('telescope.login');
        }

        return $next($request);
    }
}
