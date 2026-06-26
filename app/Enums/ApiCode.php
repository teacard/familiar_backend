<?php

namespace App\Enums;

enum ApiCode: string
{
    /** 帳號或密碼錯誤 */
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    /** 登入嘗試次數過多 */
    case TOO_MANY_LOGIN_ATTEMPTS = 'TOO_MANY_LOGIN_ATTEMPTS';
    /** 無權存取 Telescope */
    case TELESCOPE_ACCESS_FORBIDDEN = 'TELESCOPE_ACCESS_FORBIDDEN';
    /** 角色已被 admin 帳號使用，無法刪除 */
    case ROLE_IN_USE = 'ROLE_IN_USE';

    /** 已發佈的公告不可修改發佈時間、到期時間與目標對象 */
    case ANNOUNCEMENT_PUBLISHED_IMMUTABLE = 'ANNOUNCEMENT_PUBLISHED_IMMUTABLE';
    /** 已發佈的公告不可刪除 */
    case ANNOUNCEMENT_PUBLISHED_UNDELETABLE = 'ANNOUNCEMENT_PUBLISHED_UNDELETABLE';
    /** 置頂數量已達上限 */
    case ANNOUNCEMENT_PIN_LIMIT_REACHED = 'ANNOUNCEMENT_PIN_LIMIT_REACHED';
}
