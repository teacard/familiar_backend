<?php

return [
    'INVALID_CREDENTIALS' => '帳號或密碼錯誤',
    'TOO_MANY_LOGIN_ATTEMPTS' => '嘗試次數過多，請於 :seconds 秒後再試。',

    'TELESCOPE_ACCESS_FORBIDDEN' => '此帳號無權存取 Telescope',

    /** 角色管理 */
    'ROLE_IN_USE' => '此角色已被帳號使用，無法刪除',

    /** 玩家管理 */
    'PLAYER_NOT_SUSPENDED' => '僅停用狀態的玩家可刪除，請先停用該帳號',

    /** 公告管理 */
    'ANNOUNCEMENT_PUBLISHED_IMMUTABLE' => '已發佈的公告不可修改發佈時間、到期時間與目標對象',
    'ANNOUNCEMENT_PUBLISHED_UNDELETABLE' => '已發佈的公告不可刪除',
    'ANNOUNCEMENT_PIN_LIMIT_REACHED' => '置頂數量已達上限，請先取消其他置頂',
];
