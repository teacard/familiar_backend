<?php

namespace App\Enums\Item;

enum Status: string
{
    /** 啟用 */
    case ACTIVE = 'active';
    /** 停用 */
    case DISABLED = 'disabled';

    public const string SWAGGER_API_ENUM_PROPERTY = 'item.status';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '啟用' => self::ACTIVE,
        '停用' => self::DISABLED,
    ];
}
