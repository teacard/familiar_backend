<?php

use App\Enums\ApiCode;

return [
    ApiCode::INVALID_CREDENTIALS->value => '帳號或密碼錯誤',
    ApiCode::TOO_MANY_LOGIN_ATTEMPTS->value => '嘗試次數過多，請於 :seconds 秒後再試。',
    ApiCode::RECAPTCHA_VERIFICATION_FAILED->value => 'reCAPTCHA 驗證失敗，請重新整理頁面後再試一次',

    ApiCode::TELESCOPE_ACCESS_FORBIDDEN->value => '此帳號無權存取 Telescope',

    /** 角色管理 */
    ApiCode::ROLE_IN_USE->value => '此角色已被帳號使用，無法刪除',

    /** 玩家管理 */
    ApiCode::PLAYER_NOT_SUSPENDED->value => '僅停用狀態的玩家可刪除，請先停用該帳號',

    /** 公告管理 */
    ApiCode::ANNOUNCEMENT_PUBLISHED_IMMUTABLE->value => '已發佈的公告不可修改發佈時間、到期時間與目標對象',
    ApiCode::ANNOUNCEMENT_PUBLISHED_UNDELETABLE->value => '已發佈的公告不可刪除',
    ApiCode::ANNOUNCEMENT_PIN_LIMIT_REACHED->value => '置頂數量已達上限，請先取消其他置頂',

    /** 商品類別管理 */
    ApiCode::PRODUCT_TYPE_IN_USE->value => '此商品類別已被商品使用，無法刪除',

    /** 道具管理 */
    ApiCode::ITEM_IN_USE->value => '此道具已被商品獎勵明細使用，無法刪除',
];
