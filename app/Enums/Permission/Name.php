<?php

namespace App\Enums\Permission;

enum Name: string
{
    /** 查看使用者 */
    case VIEW_USERS = 'view_users';
    /** 新增使用者 */
    case CREATE_USERS = 'create_users';
    /** 編輯使用者 */
    case EDIT_USERS = 'edit_users';
    /** 刪除使用者 */
    case DELETE_USERS = 'delete_users';

    /** 查看玩家 */
    case VIEW_PLAYERS = 'view_players';
    /** 新增玩家 */
    case CREATE_PLAYERS = 'create_players';
    /** 編輯玩家 */
    case EDIT_PLAYERS = 'edit_players';
    /** 刪除玩家 */
    case DELETE_PLAYERS = 'delete_players';

    /** 查看角色 */
    case VIEW_ROLES = 'view_roles';
    /** 指派角色 */
    case ASSIGN_ROLES = 'assign_roles';

    /** 查看權限 */
    case VIEW_PERMISSIONS = 'view_permissions';
    /** 管理權限 */
    case MANAGE_PERMISSIONS = 'manage_permissions';

    /** 查看公告 */
    case VIEW_ANNOUNCEMENTS = 'view_announcements';
    /** 新增公告 */
    case CREATE_ANNOUNCEMENTS = 'create_announcements';
    /** 編輯公告 */
    case EDIT_ANNOUNCEMENTS = 'edit_announcements';
    /** 刪除公告 */
    case DELETE_ANNOUNCEMENTS = 'delete_announcements';

    /** 查看商品 */
    case VIEW_PRODUCTS = 'view_products';
    /** 新增商品 */
    case CREATE_PRODUCTS = 'create_products';
    /** 編輯商品 */
    case EDIT_PRODUCTS = 'edit_products';
    /** 刪除商品 */
    case DELETE_PRODUCTS = 'delete_products';

    /** 查看商品類別 */
    case VIEW_PRODUCT_TYPES = 'view_product_types';
    /** 新增商品類別 */
    case CREATE_PRODUCT_TYPES = 'create_product_types';
    /** 編輯商品類別 */
    case EDIT_PRODUCT_TYPES = 'edit_product_types';
    /** 刪除商品類別 */
    case DELETE_PRODUCT_TYPES = 'delete_product_types';

    public const string SWAGGER_API_ENUM_PROPERTY = 'permission.name';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '查看使用者' => self::VIEW_USERS,
        '新增使用者' => self::CREATE_USERS,
        '編輯使用者' => self::EDIT_USERS,
        '刪除使用者' => self::DELETE_USERS,

        '查看玩家' => self::VIEW_PLAYERS,
        '新增玩家' => self::CREATE_PLAYERS,
        '編輯玩家' => self::EDIT_PLAYERS,
        '刪除玩家' => self::DELETE_PLAYERS,

        '查看角色' => self::VIEW_ROLES,
        '指派角色' => self::ASSIGN_ROLES,
        '查看權限' => self::VIEW_PERMISSIONS,
        '管理權限' => self::MANAGE_PERMISSIONS,

        '查看公告' => self::VIEW_ANNOUNCEMENTS,
        '新增公告' => self::CREATE_ANNOUNCEMENTS,
        '編輯公告' => self::EDIT_ANNOUNCEMENTS,
        '刪除公告' => self::DELETE_ANNOUNCEMENTS,

        '查看商品' => self::VIEW_PRODUCTS,
        '新增商品' => self::CREATE_PRODUCTS,
        '編輯商品' => self::EDIT_PRODUCTS,
        '刪除商品' => self::DELETE_PRODUCTS,

        '查看商品類別' => self::VIEW_PRODUCT_TYPES,
        '新增商品類別' => self::CREATE_PRODUCT_TYPES,
        '編輯商品類別' => self::EDIT_PRODUCT_TYPES,
        '刪除商品類別' => self::DELETE_PRODUCT_TYPES,
    ];
}
