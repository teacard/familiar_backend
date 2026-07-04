<?php

namespace App\Data\AdminApi\Response\Media;

use Spatie\LaravelData\Data;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResponse extends Data
{
    public function __construct(
        public int $id,
        public string $url,
    ) {
    }

    public static function fromMedia(Media $media): self
    {
        return new self(
            id: $media->id,
            url: $media->getFullUrl(),
        );
    }
}
