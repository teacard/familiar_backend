<?php

namespace App\Data\AdminApi\Response\Announcement;

use App\Models\Announcement;
use Spatie\LaravelData\Data;

class AnnouncementResponse extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public string $targetAudience,
        public string $publishAt,
        public ?string $expiresAt,
        public bool $isPinned,
    ) {
    }

    public static function fromModel(Announcement $announcement): self
    {
        return new self(
            title: $announcement->title,
            content: $announcement->content,
            targetAudience: $announcement->target_audience->value,
            publishAt: $announcement->publish_at->toDateTimeString(),
            expiresAt: $announcement->expires_at?->toDateTimeString(),
            isPinned: $announcement->is_pinned,
        );
    }
}
