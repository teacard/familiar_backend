<?php

namespace Tests\Feature\Seeders;

use App\Enums\Auth\Guard;
use App\Models\Admin;
use App\Models\Role;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    /** 執行後建立系統角色與超級管理員，且系統角色擁有全部後台權限 */
    public function testCreatesSystemRoleAndSuperAdmin(): void
    {
        // GIVEN 權限已建立
        $this->seed(PermissionSeeder::class);

        // WHEN  執行 AdminSeeder
        $this->seed(AdminSeeder::class);

        // THEN  系統角色與超級管理員皆已建立，且角色含全部後台權限
        $this->assertDatabaseHas('roles', [
            'name' => AdminSeeder::NAME,
            'guard_name' => Guard::ADMIN->value,
            'is_system' => true,
        ]);
        $this->assertDatabaseHas('admins', [
            'email' => 'root@example.com',
            'is_super_admin' => true,
        ]);

        $role = Role::where('is_system', true)->first();
        $admin = Admin::where('email', 'root@example.com')->first();

        $this->assertSame(
            Permission::where('guard_name', Guard::ADMIN->value)->count(),
            $role->permissions()->count(),
        );
        $this->assertTrue($admin->hasRole($role));
    }

    /** 重複執行不產生重複的系統角色與超級管理員 */
    public function testDoesNotDuplicateOnRerun(): void
    {
        // GIVEN 權限已建立，AdminSeeder 已執行過一次
        $this->seed(PermissionSeeder::class);
        $this->seed(AdminSeeder::class);

        // WHEN  再次執行 AdminSeeder
        $this->seed(AdminSeeder::class);

        // THEN  系統角色與超級管理員各維持一筆
        $this->assertSame(
            1,
            Role::where('is_system', true)->where('guard_name', Guard::ADMIN->value)->count(),
        );
        $this->assertSame(1, Admin::where('email', 'root@example.com')->count());
    }

    /** 系統角色名稱被修改後，再次執行仍同步同一角色（以 is_system 為準，非 name） */
    public function testSyncsSystemRoleByIsSystemNotName(): void
    {
        // GIVEN 權限已建立，AdminSeeder 已執行過一次
        $this->seed(PermissionSeeder::class);
        $this->seed(AdminSeeder::class);
        $role = Role::where('is_system', true)->first();

        // 系統角色名稱被竄改
        $role->update(['name' => '被竄改的名稱']);

        // WHEN  再次執行 AdminSeeder
        $this->seed(AdminSeeder::class);

        // THEN  仍只有一個系統角色，且名稱被還原（同步的是同一筆，而非依 name 新建）
        $this->assertSame(1, Role::where('is_system', true)->count());
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => AdminSeeder::NAME,
            'is_system' => true,
        ]);
        $this->assertDatabaseMissing('roles', ['name' => '被竄改的名稱']);
    }
}
