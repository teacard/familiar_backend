<?php

use Spatie\Permission\DefaultTeamResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         *
         * 使用本套件的 "HasPermissions" trait 時，需指定用於取得權限的 Eloquent 模型。
         * 通常使用內建的 "Permission" 模型，但你可以自訂任何符合需求的模型。
         * 自訂模型必須實作 `Spatie\Permission\Contracts\Permission` 介面。
         */

        'permission' => Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         *
         * 使用本套件的 "HasRoles" trait 時，需指定用於取得角色的 Eloquent 模型。
         * 通常使用內建的 "Role" 模型，但你可以自訂任何符合需求的模型。
         * 自訂模型必須實作 `Spatie\Permission\Contracts\Role` 介面。
         */

        'role' => Role::class,

        /*
         * When using the "Teams" feature from this package, we need to know which
         * Eloquent model should be used to retrieve your teams. Of course, it
         * is often just the "Team" model but you may use whatever you like.
         *
         * 使用本套件的「團隊」功能時，需指定用於取得團隊資料的 Eloquent 模型。
         * 通常使用 "Team" 模型，但你可以自訂任何符合需求的模型。
         */
        'team' => null,

        /*
         * When using the "HasModels" trait and passing raw IDs to syncModels,
         * attachModels, or detachModels, this model class will be used to
         * resolve those IDs. If null, defaults to the guard's model.
         *
         * 使用 "HasModels" trait 並傳入原始 ID 給 syncModels、attachModels 或 detachModels 時，
         * 此模型類別將用於解析這些 ID。若設為 null，則預設使用 guard 對應的模型。
         */
        'default_model' => null,
    ],

    'table_names' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         *
         * 使用本套件的 "HasRoles" trait 時，需指定儲存角色資料的資料表名稱。
         * 預設值為 'roles'，可依需求自行修改。
         */

        'roles' => 'roles',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         *
         * 使用本套件的 "HasPermissions" trait 時，需指定儲存權限資料的資料表名稱。
         * 預設值為 'permissions'，可依需求自行修改。
         */

        'permissions' => 'permissions',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your models permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         *
         * 使用本套件的 "HasPermissions" trait 時，需指定儲存「模型對應權限」關聯的資料表名稱。
         * 預設值為 'model_has_permissions'，可依需求自行修改。
         */

        'model_has_permissions' => 'model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         *
         * 使用本套件的 "HasRoles" trait 時，需指定儲存「模型對應角色」關聯的資料表名稱。
         * 預設值為 'model_has_roles'，可依需求自行修改。
         */

        'model_has_roles' => 'model_has_roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         *
         * 使用本套件的 "HasRoles" trait 時，需指定儲存「角色對應權限」關聯的資料表名稱。
         * 預設值為 'role_has_permissions'，可依需求自行修改。
         */

        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        /*
         * Change this if you want to name the related pivots other than defaults
         *
         * 若需要自訂關聯樞紐表的欄位名稱，可在此修改（預設分別為 role_id 與 permission_id）。
         */
        'role_pivot_key' => null, // default 'role_id',
        'permission_pivot_key' => null, // default 'permission_id',

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         *
         * 若模型主鍵欄位名稱不是預設的 `model_id`，可在此自訂。
         * 例如：若主鍵使用 UUID 格式，可將此值設為 `model_uuid`。
         */

        'model_morph_key' => 'model_id',

        /*
         * Change this if you want to use the teams feature and your related model's
         * foreign key is other than `team_id`.
         *
         * 若啟用團隊功能且團隊外鍵欄位名稱不是預設的 `team_id`，可在此自訂。
         */

        'team_foreign_key' => 'team_id',
    ],

    /*
     * When set to true, the method for checking permissions will be registered on the gate.
     * Set this to false if you want to implement custom logic for checking permissions.
     *
     * 設為 true 時，權限檢查方法會自動註冊到 Laravel Gate 上。
     * 若需要實作自訂的權限驗證邏輯，請設為 false。
     */

    'register_permission_check_method' => true,

    /*
     * When set to true, Laravel\Octane\Events\OperationTerminated event listener will be registered
     * this will refresh permissions on every TickTerminated, TaskTerminated and RequestTerminated
     * NOTE: This should not be needed in most cases, but an Octane/Vapor combination benefited from it.
     *
     * 設為 true 時，會註冊 Laravel Octane 的 OperationTerminated 事件監聽器，
     * 在每次 TickTerminated、TaskTerminated 與 RequestTerminated 時自動重新整理權限快取。
     * 注意：大多數情況不需要開啟，主要用於 Octane 搭配 Vapor 的部署環境。
     */
    'register_octane_reset_listener' => false,

    /*
     * Events will fire when a role or permission is assigned/unassigned:
     * \Spatie\Permission\Events\RoleAttachedEvent
     * \Spatie\Permission\Events\RoleDetachedEvent
     * \Spatie\Permission\Events\PermissionAttachedEvent
     * \Spatie\Permission\Events\PermissionDetachedEvent
     *
     * To enable, set to true, and then create listeners to watch these events.
     *
     * 指派或移除角色／權限時會觸發以下事件：
     * \Spatie\Permission\Events\RoleAttachedEvent（角色已指派）
     * \Spatie\Permission\Events\RoleDetachedEvent（角色已移除）
     * \Spatie\Permission\Events\PermissionAttachedEvent（權限已指派）
     * \Spatie\Permission\Events\PermissionDetachedEvent（權限已移除）
     *
     * 設為 true 後，需自行建立對應的事件監聽器來處理這些事件。
     */
    'events_enabled' => false,

    /*
     * Teams Feature.
     * When set to true the package implements teams using the 'team_foreign_key'.
     * If you want the migrations to register the 'team_foreign_key', you must
     * set this to true before doing the migration.
     * If you already did the migration then you must make a new migration to also
     * add 'team_foreign_key' to 'roles', 'model_has_roles', and 'model_has_permissions'
     * (view the latest version of this package's migration file)
     *
     * 團隊功能開關。
     * 設為 true 時，套件會啟用以 'team_foreign_key' 為基礎的多團隊支援。
     * 若希望遷移檔自動加入 'team_foreign_key' 欄位，必須在執行遷移前先將此值設為 true。
     * 若已執行過遷移，需另建新的遷移檔，手動在 'roles'、'model_has_roles'、'model_has_permissions'
     * 資料表中加入 'team_foreign_key' 欄位（請參考套件最新版本的遷移範本）。
     */

    'teams' => false,

    /*
     * The class to use to resolve the permissions team id
     *
     * 指定用於解析權限所屬團隊 ID 的類別。
     */
    'team_resolver' => DefaultTeamResolver::class,

    /*
     * Passport Client Credentials Grant
     * When set to true the package will use Passports Client to check permissions
     *
     * Passport 用戶端憑證授權模式。
     * 設為 true 時，套件將使用 Laravel Passport 的 Client 來進行權限驗證。
     */

    'use_passport_client_credentials' => false,

    /*
     * When set to true, the required permission names are added to exception messages.
     * This could be considered an information leak in some contexts, so the default
     * setting is false here for optimum safety.
     *
     * 設為 true 時，例外訊息中會包含所需的權限名稱，方便除錯。
     * 但此設定在某些情境下可能造成資訊洩漏，基於安全考量預設為 false。
     */

    'display_permission_in_exception' => false,

    /*
     * When set to true, the required role names are added to exception messages.
     * This could be considered an information leak in some contexts, so the default
     * setting is false here for optimum safety.
     *
     * 設為 true 時，例外訊息中會包含所需的角色名稱，方便除錯。
     * 但此設定在某些情境下可能造成資訊洩漏，基於安全考量預設為 false。
     */

    'display_role_in_exception' => false,

    /*
     * By default wildcard permission lookups are disabled.
     * See documentation to understand supported syntax.
     *
     * 萬用字元權限查詢預設為停用。
     * 啟用後可使用萬用字元語法進行彈性的權限比對，詳細語法請參考官方文件。
     */

    'enable_wildcard_permission' => false,

    /*
     * The class to use for interpreting wildcard permissions.
     * If you need to modify delimiters, override the class and specify its name here.
     *
     * 指定用於解析萬用字元權限的類別。
     * 若需要自訂分隔符號，可繼承該類別並在此指定自訂類別名稱。
     */
    // 'wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    /* Cache-specific settings */
    /* 快取相關設定 */

    'cache' => [

        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         *
         * 預設情況下，所有權限會快取 24 小時以提升效能。
         * 當權限或角色資料有更新時，快取會自動清除。
         */

        'expiration_time' => DateInterval::createFromDateString('1 hour'),

        /*
         * The cache key used to store all permissions.
         *
         * 用於儲存所有權限快取資料的快取鍵名。
         */

        'key' => 'spatie.permission.cache',

        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         *
         * 可選擇性地指定用於權限與角色快取的快取驅動（對應 cache.php 中的 store 設定）。
         * 設為 'default' 表示使用 cache.php 中的預設快取驅動。
         */

        'store' => 'default',
    ],
];
