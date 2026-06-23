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
    /** 玩家帳號非停用狀態，無法刪除 */
    case PLAYER_NOT_SUSPENDED = 'PLAYER_NOT_SUSPENDED';
}
