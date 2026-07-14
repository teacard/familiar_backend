<?php

namespace App\Data\Api\Response\Registration;

use App\Enums\Media\CollectionName;
use App\Models\DraftPlayer;
use Spatie\LaravelData\Data;

class RegistrationStatusResponse extends Data
{
    public function __construct(
        public int $registrationStep,
        public string $email,
        public ?string $name,
        public ?string $avatarUrl,
    ) {
    }

    public static function fromModel(DraftPlayer $draft): self
    {
        return new self(
            registrationStep: $draft->registration_step->value,
            email: $draft->email,
            name: $draft->name,
            avatarUrl: $draft->getFirstMediaUrl(CollectionName::PLAYER->value) ?: null,
        );
    }
}
