<?php

use App\Enums\Permission\Name;

return [
    'name' => [
        /** 前台使用者管理 */
        Name::VIEW_USERS->value => 'View Users',
        Name::CREATE_USERS->value => 'Create Users',
        Name::EDIT_USERS->value => 'Edit Users',
        Name::DELETE_USERS->value => 'Delete Users',

        /** 玩家管理 */
        Name::VIEW_PLAYERS->value => 'View Players',
        Name::CREATE_PLAYERS->value => 'Create Players',
        Name::EDIT_PLAYERS->value => 'Edit Players',
        Name::DELETE_PLAYERS->value => 'Delete Players',

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

        /** 商品管理 */
        Name::VIEW_PRODUCTS->value => 'View Products',
        Name::CREATE_PRODUCTS->value => 'Create Products',
        Name::EDIT_PRODUCTS->value => 'Edit Products',
        Name::DELETE_PRODUCTS->value => 'Delete Products',

        /** 商品類別管理 */
        Name::VIEW_PRODUCT_TYPES->value => 'View Product Types',
        Name::CREATE_PRODUCT_TYPES->value => 'Create Product Types',
        Name::EDIT_PRODUCT_TYPES->value => 'Edit Product Types',
        Name::DELETE_PRODUCT_TYPES->value => 'Delete Product Types',
    ],
];
