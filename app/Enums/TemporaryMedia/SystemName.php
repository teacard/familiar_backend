<?php

namespace App\Enums\TemporaryMedia;

enum SystemName: string
{
    /** 後台系統：後台上傳的暫存媒體掛在此 owner */
    case ADMIN = 'admin';
}
