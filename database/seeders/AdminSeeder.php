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
        $role = Role::updateOrCreate(
            ['name' => self::NAME, 'guard_name' => Guard::ADMIN->value],
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
            ]
        );

        $admin->syncRoles(self::NAME);
    }
}
