<?php

namespace App\Enums\TemporaryMedia;

enum SystemName: string
{
    /** 後台系統：後台上傳的暫存媒體掛在此 owner */
    case ADMIN = 'admin';

    /** 玩家自助註冊系統：Step③選圖後立即上傳的暫存媒體掛在此 owner，待送出時才 transferToModel 轉綁到草稿 */
    case PLAYER = 'player';
}
