<?php

namespace Database\Seeders;

use App\Enums\Admin\Status;
use App\Enums\Auth\Guard;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public const NAME = '超級管理員';

    public function run(): void
    {
        // 以 is_system 作為系統角色的穩定識別，name 可後續被修改而不影響同步
        $role = Role::updateOrCreate(
            ['is_system' => true, 'guard_name' => Guard::ADMIN->value],
            ['name' => self::NAME],
        );

        $role->syncPermissions(
            Permission::where('guard_name', Guard::ADMIN->value)->pluck('name')->all()
        );

        $admin = Admin::updateOrCreate(
            ['email' => 'root@example.com'],
            [
                'name' => self::NAME,
                'password' => config('admin.seed_password'),
                'status' => Status::ACTIVE,
                'is_super_admin' => true,
            ]
        );

        $admin->syncRoles($role);
    }
}
