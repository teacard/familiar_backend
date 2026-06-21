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
