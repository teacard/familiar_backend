<?php

namespace App\Data\AdminApi\Request\Announcement;

use App\Enums\Announcement\TargetAudience;
use App\Http\Requests\AdminApi\Announcement\StoreRequest;

readonly class StoreRequestData
{
    public function __construct(
        public string $title,
        public string $content,
        public TargetAudience $targetAudience,
        public string $publishAt,
        public ?string $expiresAt,
        public bool $shouldPin,
    ) {
    }

    public static function fromRequest(StoreRequest $request): self
    {
        return new self(
            title: $request->title,
            content: $request->content,
            targetAudience: TargetAudience::from($request->targetAudience),
            publishAt: $request->publishAt,
            expiresAt: $request->expiresAt,
            shouldPin: (bool)$request->shouldPin,
        );
    }
}
