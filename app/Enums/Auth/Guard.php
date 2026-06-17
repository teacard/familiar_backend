<?php

namespace App\Enums\Auth;

enum Guard: string
{
    case ADMIN = 'admin';
    case WEB = 'web';
}
