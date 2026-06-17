<?php

namespace App\Enums\Auth;

enum ApiCode: string
{
    /** 帳號或密碼錯誤 */
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
}
