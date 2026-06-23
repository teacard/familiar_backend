<?php

namespace App\Data\AdminApi\Response\Player;

use App\Models\Player;
use Spatie\LaravelData\Data;

class PlayerResponse extends Data
{
    public function __construct(
        public int $id,
        public string $playerNumber,
        public string $name,
        public string $email,
        public string $status,
        public ?string $createdDate,
    ) {
    }

    public static function fromModel(Player $player): self
    {
        return new self(
            id: $player->id,
            playerNumber: $player->player_number,
            name: $player->name,
            email: $player->email,
            status: $player->status->value,
            createdDate: $player->created_at?->toDateString(),
        );
    }
}
