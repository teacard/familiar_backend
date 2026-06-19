<?php

namespace App\Enums\Auth;

enum Guard: string
{
    case ADMIN = 'admin';
    case WEB = 'web';
    /** Telescope 後台 web session 登入專用 guard */
    case ADMIN_WEB = 'admin_web';
}
