<?php

use App\Enums\Permission\Name;

return [
    'name' => [
        /** 前台使用者管理 */
        Name::VIEW_USERS->value => 'View Users',
        Name::CREATE_USERS->value => 'Create Users',
        Name::EDIT_USERS->value => 'Edit Users',
        Name::DELETE_USERS->value => 'Delete Users',

        /** 角色管理 */
        Name::VIEW_ROLES->value => 'View Roles',
        Name::ASSIGN_ROLES->value => 'Assign Roles',
        Name::VIEW_PERMISSIONS->value => 'View Permissions',
        Name::MANAGE_PERMISSIONS->value => 'Manage Permissions',

        /** 公告管理 */
        Name::VIEW_ANNOUNCEMENTS->value => 'View Announcements',
        Name::CREATE_ANNOUNCEMENTS->value => 'Create Announcements',
        Name::EDIT_ANNOUNCEMENTS->value => 'Edit Announcements',
        Name::DELETE_ANNOUNCEMENTS->value => 'Delete Announcements',
    ],
];
