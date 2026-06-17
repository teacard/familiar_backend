<?php

namespace App\Data\AdminApi\Response\Admin;

use App\Models\Admin;
use Spatie\LaravelData\Data;

class AdminResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public string $status,
        public ?string $lastLoginDate,
    ) {
    }

    public static function fromModel(Admin $admin): self
    {
        return new self(
            id: $admin->id,
            name: $admin->name,
            email: $admin->email,
            role: $admin->getRoleNames()->first(),
            status: $admin->status->value,
            lastLoginDate: $admin->last_login_date?->toDateString(),
        );
    }
}
