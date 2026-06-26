<?php

namespace App\Enums\Announcement;

enum Status: string
{
    /** 草稿 */
    case DRAFT = 'DRAFT';
    /** 排程（待發布） */
    case SCHEDULED = 'SCHEDULED';
    /** 已發布 */
    case PUBLISHED = 'PUBLISHED';
    /** 已到期 */
    case EXPIRED = 'EXPIRED';

    public const string SWAGGER_API_ENUM_PROPERTY = 'announcement.status';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '草稿' => self::DRAFT,
        '排程' => self::SCHEDULED,
        '已發布' => self::PUBLISHED,
        '已到期' => self::EXPIRED,
    ];
}
