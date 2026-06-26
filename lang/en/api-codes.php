<?php

return [
    'INVALID_CREDENTIALS' => 'Invalid credentials.',
    'TOO_MANY_LOGIN_ATTEMPTS' => 'Too many attempts. Please try again in :seconds seconds.',

    'TELESCOPE_ACCESS_FORBIDDEN' => 'This account is not allowed to access Telescope.',

    /** 角色管理 */
    'ROLE_IN_USE' => 'This role is in use by an account and cannot be deleted.',

    /** 玩家管理 */
    'PLAYER_NOT_SUSPENDED' => 'Only suspended players can be deleted. Please suspend the account first.',

    /** 公告管理 */
    'ANNOUNCEMENT_PUBLISHED_IMMUTABLE' => 'A published announcement cannot change its publish time, expiry time, or target audience.',
    'ANNOUNCEMENT_PUBLISHED_UNDELETABLE' => 'A published announcement cannot be deleted.',
    'ANNOUNCEMENT_PIN_LIMIT_REACHED' => 'The pin limit has been reached. Please unpin another announcement first.',
];
