<?php

namespace App\Data\AdminApi\Response\Player;

use App\Models\Player;
use Spatie\LaravelData\Data;

class PlayerShowResponse extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public string $status,
    ) {
    }

    public static function fromModel(Player $player): self
    {
        return new self(
            name: $player->name,
            email: $player->email,
            phone: $player->phone,
            status: $player->status->value,
        );
    }
}
