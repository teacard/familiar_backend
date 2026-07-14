<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 每日清除超過 48 小時的 Telescope 觀測資料，避免無限成長
Schedule::command('telescope:prune --hours=48')->daily();

// 每日清除暫存集合中建立超過 1 天的媒體，避免未被採用的上傳檔案殘留
Schedule::command('media:prune-temporary')->daily();

// 每日清除超過 1 天未更新的玩家自助註冊草稿，避免半成品資料無限累積
Schedule::command('players:prune-draft')->daily();

// 每分鐘依發布/到期時間自動轉換公告狀態（SCHEDULED → PUBLISHED → EXPIRED）
Schedule::command('announcements:transition-statuses')->everyMinute();

// 每日刪除到期時間已超過 6 個月的公告，避免資料無限成長
Schedule::command('announcements:prune-expired')->daily();
