<?php

use App\Enums\Permission\Name;

return [
    'name' => [
        /** 前台使用者管理 */
        Name::VIEW_USERS->value => '查看使用者',
        Name::CREATE_USERS->value => '新增使用者',
        Name::EDIT_USERS->value => '編輯使用者',
        Name::DELETE_USERS->value => '刪除使用者',

        /** 玩家管理 */
        Name::VIEW_PLAYERS->value => '查看玩家',
        Name::CREATE_PLAYERS->value => '新增玩家',
        Name::EDIT_PLAYERS->value => '編輯玩家',
        Name::DELETE_PLAYERS->value => '刪除玩家',

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

        /** 商品管理 */
        Name::VIEW_PRODUCTS->value => '查看商品',
        Name::CREATE_PRODUCTS->value => '新增商品',
        Name::EDIT_PRODUCTS->value => '編輯商品',
        Name::DELETE_PRODUCTS->value => '刪除商品',

        /** 商品類別管理 */
        Name::VIEW_PRODUCT_TYPES->value => '查看商品類別',
        Name::CREATE_PRODUCT_TYPES->value => '新增商品類別',
        Name::EDIT_PRODUCT_TYPES->value => '編輯商品類別',
        Name::DELETE_PRODUCT_TYPES->value => '刪除商品類別',
    ],
];
