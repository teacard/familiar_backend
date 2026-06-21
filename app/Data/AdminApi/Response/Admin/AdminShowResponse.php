<?php

namespace App\Data\AdminApi\Response\Admin;

use App\Enums\Media\CollectionName;
use App\Models\Admin;
use Spatie\LaravelData\Data;

class AdminShowResponse extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $role,
        public string $status,
        public ?string $avatarUrl,
    ) {
    }

    public static function fromModel(Admin $admin): self
    {
        return new self(
            name: $admin->name,
            email: $admin->email,
            role: $admin->getRoleNames()->first(),
            status: $admin->status->value,
            avatarUrl: $admin->getFirstMediaUrl(CollectionName::ADMIN->value) ?: null,
        );
    }
}
