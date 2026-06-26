<?php

namespace App\Console\Commands;

use App\Enums\Announcement\Status;
use App\Repositories\Applications\Announcement\AnnouncementRepository;
use Illuminate\Console\Command;

class TransitionAnnouncementStatuses extends Command
{
    protected $signature = 'announcements:transition-statuses';

    protected $description = '依發布/到期時間自動將公告轉為 PUBLISHED / EXPIRED';

    public function handle(AnnouncementRepository $repository): int
    {
        $now = now()->toDateTimeString();

        $published = $repository->update(
            filters: [
                'status' => Status::SCHEDULED->value,
                'publishAtEnd' => $now,
            ],
            attributes: ['status' => Status::PUBLISHED->value],
        );

        $expired = $repository->update(
            filters: [
                'status' => Status::PUBLISHED->value,
                'expiresAtLte' => $now,
            ],
            attributes: ['status' => Status::EXPIRED->value],
        );

        $this->info("已發布 {$published} 筆、到期 {$expired} 筆公告。");

        return self::SUCCESS;
    }
}
