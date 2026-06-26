<?php

namespace App\Console\Commands;

use App\Repositories\Applications\Announcement\AnnouncementRepository;
use Illuminate\Console\Command;

class PruneExpiredAnnouncements extends Command
{
    protected $signature = 'announcements:prune-expired';

    protected $description = '刪除到期時間已超過 6 個月的公告';

    public function handle(AnnouncementRepository $repository): int
    {
        $deleted = $repository->delete([
            'expiresAtLt' => now()->subMonths(6)->toDateTimeString(),
        ]);

        $this->info("已刪除 {$deleted} 筆逾期超過 6 個月的公告。");

        return self::SUCCESS;
    }
}
