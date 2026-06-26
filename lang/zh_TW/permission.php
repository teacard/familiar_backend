<?php

use App\Enums\Permission\Name;

return [
    'name' => [
        /** 前台使用者管理 */
        Name::VIEW_USERS->value => '查看使用者',
        Name::CREATE_USERS->value => '新增使用者',
        Name::EDIT_USERS->value => '編輯使用者',
        Name::DELETE_USERS->value => '刪除使用者',

        /** 角色管理 */
        Name::VIEW_ROLES->value => '查看角色',
        Name::ASSIGN_ROLES->value => '指派角色',
        Name::VIEW_PERMISSIONS->value => '查看權限',
        Name::MANAGE_PERMISSIONS->value => '管理權限',

        /** 公告管理 */
        Name::VIEW_ANNOUNCEMENTS->value => '查看公告',
        Name::CREATE_ANNOUNCEMENTS->value => '新增公告',
        Name::EDIT_ANNOUNCEMENTS->value => '編輯公告',
        Name::DELETE_ANNOUNCEMENTS->value => '刪除公告',
    ],
];
