<?php

namespace App\Console\Commands;

use App\Services\MediaService;
use Illuminate\Console\Command;

class PruneTemporaryMedia extends Command
{
    protected $signature = 'media:prune-temporary';

    protected $description = '清除暫存集合中建立超過 1 天的媒體（連同實體檔案）';

    public function handle(MediaService $mediaService): int
    {
        $deleted = $mediaService->pruneExpiredTemporary(now()->subDay());

        $this->info("已清除 {$deleted} 筆逾期暫存媒體。");

        return self::SUCCESS;
    }
}
