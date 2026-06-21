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
        public ?AdminAvatarResponse $avatar,
    ) {
    }

    public static function fromModel(Admin $admin): self
    {
        $firstMedia = $admin->getFirstMedia(CollectionName::ADMIN->value);

        return new self(
            name: $admin->name,
            email: $admin->email,
            role: $admin->getRoleNames()->first(),
            status: $admin->status->value,
            avatar: $firstMedia ? AdminAvatarResponse::fromMedia($firstMedia) : null,
        );
    }
}
