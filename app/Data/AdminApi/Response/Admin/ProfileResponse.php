<?php

namespace App\Data\AdminApi\Response\Admin;

use App\Enums\Media\CollectionName;
use App\Models\Admin;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

class ProfileResponse extends Data
{
    public function __construct(
        public string $name,
        public string $photo,
        /** @var array<int, string> */
        public array $permissions,
    ) {}

    public static function fromModel(Admin $admin): self
    {
        return new self(
            name: $admin->name,
            // 有頭像回該媒體完整 URL；無頭像時回 public 的預設頭像 svg
            photo: $admin->getFirstMediaUrl(CollectionName::ADMIN->value)
                ?: asset(config('admin.profile.default_photo')),
            // 含角色帶來與直接指派的全部權限，去重後僅回傳 name 字串
            permissions: $admin->getAllPermissions()->pluck('name')->unique()->values()->all(),
        );
    }
}
