<?php

use App\Enums\ApiCode;

return [
    ApiCode::INVALID_CREDENTIALS->value => 'Invalid credentials.',
    ApiCode::TOO_MANY_LOGIN_ATTEMPTS->value => 'Too many attempts. Please try again in :seconds seconds.',

    ApiCode::TELESCOPE_ACCESS_FORBIDDEN->value => 'This account is not allowed to access Telescope.',

    /** 角色管理 */
    ApiCode::ROLE_IN_USE->value => 'This role is in use by an account and cannot be deleted.',

    /** 玩家管理 */
    ApiCode::PLAYER_NOT_SUSPENDED->value => 'Only suspended players can be deleted. Please suspend the account first.',

    /** 公告管理 */
    ApiCode::ANNOUNCEMENT_PUBLISHED_IMMUTABLE->value => 'A published announcement cannot change its publish time, expiry time, or target audience.',
    ApiCode::ANNOUNCEMENT_PUBLISHED_UNDELETABLE->value => 'A published announcement cannot be deleted.',
    ApiCode::ANNOUNCEMENT_PIN_LIMIT_REACHED->value => 'The pin limit has been reached. Please unpin another announcement first.',

    /** 商品類別管理 */
    ApiCode::PRODUCT_TYPE_IN_USE->value => 'This product type is in use by a product and cannot be deleted.',

    /** 道具管理 */
    ApiCode::ITEM_IN_USE->value => 'This item is in use by a product reward and cannot be deleted.',
];
