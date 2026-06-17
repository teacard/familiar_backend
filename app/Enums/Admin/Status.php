<?php

namespace App\Enums\Admin;

enum Status: string
{
    /** 啟用 */
    case ACTIVE = 'active';
    /** 停用 */
    case SUSPENDED = 'suspended';

    public const string SWAGGER_API_ENUM_PROPERTY = 'admin.status';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '啟用' => self::ACTIVE,
        '停用' => self::SUSPENDED,
    ];
}
