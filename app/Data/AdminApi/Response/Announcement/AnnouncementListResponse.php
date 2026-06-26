<?php

namespace App\Data\AdminApi\Response\Announcement;

use App\Models\Announcement;
use Spatie\LaravelData\Data;

class AnnouncementListResponse extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public string $status,
        public string $publishAt,
        public ?string $expiresAt,
        public bool $isPinned,
    ) {
    }

    public static function fromModel(Announcement $announcement): self
    {
        return new self(
            id: $announcement->id,
            title: $announcement->title,
            content: $announcement->content,
            status: $announcement->status->value,
            publishAt: $announcement->publish_at->toDateTimeString(),
            expiresAt: $announcement->expires_at?->toDateTimeString(),
            isPinned: $announcement->is_pinned,
        );
    }
}
