<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
{
    /**
     * 指派此角色的 Admin。
     *
     * 透過 Spatie 的 model_has_roles 多型樞紐表反查：該表以 (role_id, model_type, model_id)
     * 記錄「某模型擁有某角色」，這裡限定 model_type 為 Admin，取出所有持有此角色的 admin。
     * 樞紐表名與欄位名皆讀取 config/permission.php，與 Spatie 設定保持一致。
     */
    public function admins(): MorphToMany
    {
        return $this->morphedByMany(
            Admin::class,                                       // 反查的目標模型（model_type = Admin）
            'model',                                            // 多型前綴（對應 model_type / model_id 欄位）
            config('permission.table_names.model_has_roles'),   // 樞紐表：model_has_roles
            app(PermissionRegistrar::class)->pivotRole,         // 樞紐表上指向 role 的外鍵（預設 role_id）
            config('permission.column_names.model_morph_key'),  // 樞紐表上指向模型的鍵（預設 model_id）
        );
    }
}
