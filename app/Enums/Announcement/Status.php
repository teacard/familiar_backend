<?php

namespace App\Enums\Announcement;

enum Status: string
{
    /** 草稿 */
    case DRAFT = 'draft';
    /** 排程（待發布） */
    case SCHEDULED = 'scheduled';
    /** 已發布 */
    case PUBLISHED = 'published';
    /** 已到期 */
    case EXPIRED = 'expired';

    public const string SWAGGER_API_ENUM_PROPERTY = 'announcement.status';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '草稿' => self::DRAFT,
        '排程' => self::SCHEDULED,
        '已發布' => self::PUBLISHED,
        '已到期' => self::EXPIRED,
    ];
}
