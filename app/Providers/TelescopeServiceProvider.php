<?php

namespace App\Providers;

use App\Enums\Admin\Status;
use App\Enums\Auth\Guard;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        // local 與測試站（develop）記錄全部；其他環境僅記錄錯誤/失敗/排程/監控標籤
        $recordAll = $this->app->environment(['local', 'develop']);

        Telescope::filter(function (IncomingEntry $entry) use ($recordAll) {
            return $recordAll
                   || $entry->isReportableException()
                   || $entry->isFailedRequest()
                   || $entry->isFailedJob()
                   || $entry->isScheduledTask()
                   || $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * 以 admin_web guard 的登入者判斷授權：須為 ACTIVE 且 is_super_admin 的 Admin。
     * 不依賴注入的預設 guard user，因 Telescope 登入走 admin_web session guard。
     *
     * 參數 $user 必須保留（可為 null）：預設 web guard 對此請求是 guest，
     * 若 callback 無可為 null 的參數，Laravel Gate 會將 guest 直接判定為拒絕（403）。
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (mixed $user = null) {
            /** @var Admin|null $admin */
            $admin = Auth::guard(Guard::ADMIN_WEB->value)->user();

            return $admin instanceof Admin
                && Status::ACTIVE === $admin->status
                && true === $admin->is_super_admin;
        });
    }
}
