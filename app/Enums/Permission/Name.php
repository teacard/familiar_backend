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

    public const string SWAGGER_API_ENUM_PROPERTY = 'permission.name';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '查看使用者' => self::VIEW_USERS,
        '新增使用者' => self::CREATE_USERS,
        '編輯使用者' => self::EDIT_USERS,
        '刪除使用者' => self::DELETE_USERS,
        '查看角色' => self::VIEW_ROLES,
        '指派角色' => self::ASSIGN_ROLES,
        '查看權限' => self::VIEW_PERMISSIONS,
        '管理權限' => self::MANAGE_PERMISSIONS,
        '查看公告' => self::VIEW_ANNOUNCEMENTS,
        '新增公告' => self::CREATE_ANNOUNCEMENTS,
        '編輯公告' => self::EDIT_ANNOUNCEMENTS,
        '刪除公告' => self::DELETE_ANNOUNCEMENTS,
    ];

    /** 取得翻譯後 label 對應 enum case 的陣列，供 Controller 回傳 */
    public static function swaggerApiEnumOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                trans("permission.name.{$case->value}") => $case,
            ])
            ->toArray();
    }
}
