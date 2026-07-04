<?php

namespace App\Enums\Announcement;

enum TargetAudience: string
{
    /** 所有使用者 */
    case ALL_USERS = 'all_users';
    /** VIP 會員 */
    case VIP = 'vip';
    /** 一般會員 */
    case GENERAL = 'general';

    public const string SWAGGER_API_ENUM_PROPERTY = 'announcement.target_audience';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '所有使用者' => self::ALL_USERS,
        'VIP 會員' => self::VIP,
        '一般會員' => self::GENERAL,
    ];
}
