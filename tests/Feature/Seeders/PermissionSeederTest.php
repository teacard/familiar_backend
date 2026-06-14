<?php

namespace Tests\Feature\Seeders;

use App\Enums\Permission\Name;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    /** Seeder 執行後，DB 應包含且僅包含 enum 定義的所有權限 */
    public function testInsertsAllDefinedPermissions(): void
    {
        // GIVEN 空的 permissions 表
        // WHEN  執行 PermissionSeeder
        $this->seed(PermissionSeeder::class);

        // THEN  DB 內的權限數量與 enum 定義一致，且每筆都存在
        $defined = collect(Name::cases())->pluck('value');

        $this->assertSame($defined->count(), Permission::count());
        foreach ($defined as $name) {
            $this->assertDatabaseHas('permissions', ['name' => $name]);
        }
    }

    /** 重複執行 Seeder 不應產生重複的權限資料 */
    public function testDoesNotDuplicateOnRerun(): void
    {
        // GIVEN Seeder 已執行過一次
        $this->seed(PermissionSeeder::class);

        // WHEN  再次執行 Seeder
        $this->seed(PermissionSeeder::class);

        // THEN  權限數量不變
        $this->assertSame(collect(Name::cases())->count(), Permission::count());
    }

    /** DB 中存在 enum 已移除的舊權限時，Seeder 應將其刪除 */
    public function testDeletesPermissionsNotInEnum(): void
    {
        // GIVEN DB 中有一筆不在 enum 定義內的舊權限
        Permission::create(['name' => 'obsolete_permission', 'guard_name' => 'web']);

        // WHEN  執行 PermissionSeeder
        $this->seed(PermissionSeeder::class);

        // THEN  舊權限被刪除
        $this->assertDatabaseMissing('permissions', ['name' => 'obsolete_permission']);
    }
}
