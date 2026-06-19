<?php

namespace App\Enums\Auth;

enum ApiCode: string
{
    /** 帳號或密碼錯誤 */
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    /** 登入嘗試次數過多 */
    case TOO_MANY_LOGIN_ATTEMPTS = 'TOO_MANY_LOGIN_ATTEMPTS';
    /** 無權存取 Telescope */
    case TELESCOPE_ACCESS_FORBIDDEN = 'TELESCOPE_ACCESS_FORBIDDEN';
}
