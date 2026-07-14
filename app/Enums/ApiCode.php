<?php

namespace App\Enums;

enum ApiCode: string
{
    /** 帳號或密碼錯誤 */
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    /** 登入嘗試次數過多 */
    case TOO_MANY_LOGIN_ATTEMPTS = 'TOO_MANY_LOGIN_ATTEMPTS';
    /** reCAPTCHA 驗證失敗 */
    case RECAPTCHA_VERIFICATION_FAILED = 'RECAPTCHA_VERIFICATION_FAILED';
    /** 無權存取 Telescope */
    case TELESCOPE_ACCESS_FORBIDDEN = 'TELESCOPE_ACCESS_FORBIDDEN';
    /** 角色已被 admin 帳號使用，無法刪除 */
    case ROLE_IN_USE = 'ROLE_IN_USE';
    /** 玩家帳號非停用狀態，無法刪除 */
    case PLAYER_NOT_SUSPENDED = 'PLAYER_NOT_SUSPENDED';

    /** 已發佈的公告不可修改發佈時間、到期時間與目標對象 */
    case ANNOUNCEMENT_PUBLISHED_IMMUTABLE = 'ANNOUNCEMENT_PUBLISHED_IMMUTABLE';
    /** 已發佈的公告不可刪除 */
    case ANNOUNCEMENT_PUBLISHED_UNDELETABLE = 'ANNOUNCEMENT_PUBLISHED_UNDELETABLE';
    /** 置頂數量已達上限 */
    case ANNOUNCEMENT_PIN_LIMIT_REACHED = 'ANNOUNCEMENT_PIN_LIMIT_REACHED';

    /** 商品類別已被商品使用，無法刪除 */
    case PRODUCT_TYPE_IN_USE = 'PRODUCT_TYPE_IN_USE';
    /** 道具已被商品獎勵明細使用，無法刪除 */
    case ITEM_IN_USE = 'ITEM_IN_USE';

    /** 玩家自助註冊：email 已是正式會員 */
    case EMAIL_ALREADY_REGISTERED = 'EMAIL_ALREADY_REGISTERED';
    /** 玩家自助註冊：查無對應草稿，或 email/token 不符 */
    case REGISTRATION_DRAFT_NOT_FOUND = 'REGISTRATION_DRAFT_NOT_FOUND';
    /** 玩家自助註冊：尚未寄送驗證碼即嘗試驗證 */
    case VERIFICATION_CODE_NOT_SENT = 'VERIFICATION_CODE_NOT_SENT';
    /** 玩家自助註冊：驗證碼不符 */
    case VERIFICATION_CODE_INVALID = 'VERIFICATION_CODE_INVALID';
    /** 玩家自助註冊：驗證碼已過期 */
    case VERIFICATION_CODE_EXPIRED = 'VERIFICATION_CODE_EXPIRED';
    /** 玩家自助註冊：驗證碼錯誤次數達上限已鎖定 */
    case VERIFICATION_CODE_LOCKED = 'VERIFICATION_CODE_LOCKED';
    /** 玩家自助註冊：距上次寄送驗證碼未滿冷卻時間 */
    case VERIFICATION_CODE_RESEND_TOO_SOON = 'VERIFICATION_CODE_RESEND_TOO_SOON';
    /** 玩家自助註冊：跳過前置步驟 */
    case REGISTRATION_STEP_SKIPPED = 'REGISTRATION_STEP_SKIPPED';
}
