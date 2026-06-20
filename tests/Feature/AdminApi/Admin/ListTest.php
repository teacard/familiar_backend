<?php

namespace Tests\Feature\AdminApi\Admin;

use App\Enums\Admin\Status;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 無篩選條件時，可成功取得後台人員分頁列表 */
    public function testReturnsAdminList(): void
    {
        // GIVEN 有 view_users 權限的管理員，以及 3 筆後台人員資料
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        Admin::factory()->count(3)->create()->each(fn ($a) => $a->assignRole($staffRole));

        // WHEN  發送 GET /admin-api/admin
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin');

        // THEN  回傳 200 並含分頁結構（不含 total）
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [['id', 'name', 'email', 'role', 'status', 'lastLoginDate']],
                    'currentPage',
                    'perPage',
                    'lastPage',
                ],
            ]);

        $this->assertArrayNotHasKey('total', $response->json('data'));
    }

    /** 關鍵字篩選只回傳 name 或 email 含關鍵字的後台人員 */
    public function testFiltersByKeyword(): void
    {
        // GIVEN 有 view_users 權限的管理員，及兩筆後台人員（一筆 name 含「王」）
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $target = Admin::factory()->create(['name' => '王小明']);
        $target->assignRole($staffRole);
        $other = Admin::factory()->create(['name' => '李阿花']);
        $other->assignRole($staffRole);

        // WHEN  帶 keyword=王 發送 GET /admin-api/admin
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin?keyword=王');

        // THEN  回傳 200，items 只含「王小明」那筆
        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
    }

    /** 狀態篩選只回傳對應狀態的後台人員 */
    public function testFiltersByStatus(): void
    {
        // GIVEN 有 view_users 權限的管理員，及 active / suspended 各一筆
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $a1 = Admin::factory()->create(['status' => Status::ACTIVE]);
        $a1->assignRole($staffRole);
        $a2 = Admin::factory()->create(['status' => Status::SUSPENDED]);
        $a2->assignRole($staffRole);

        // WHEN  帶 status=active 篩選
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin?status=active');

        // THEN  回傳 200，所有 items 的 status 皆為 active
        $response->assertOk();
        foreach ($response->json('data.items') as $item) {
            $this->assertSame('active', $item['status']);
        }
    }

    /** 角色篩選只回傳持有指定角色的後台人員 */
    public function testFiltersByRole(): void
    {
        // GIVEN 有 view_users 權限的管理員，兩筆後台人員分持不同角色
        $actor = $this->adminWith('view_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $otherRole = Role::create(['name' => 'other', 'guard_name' => 'admin']);

        $target = Admin::factory()->create();
        $target->assignRole($editorRole);
        $other = Admin::factory()->create();
        $other->assignRole($otherRole);

        // WHEN  帶 roleId 篩選
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/admin?roleId={$editorRole->id}");

        // THEN  回傳 200，只含持有 editor 角色的後台人員
        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
    }

    /** 三條件同時篩選取得交集結果 */
    public function testCombinesMultipleFilters(): void
    {
        // GIVEN 有 view_users 權限的管理員，及多筆後台人員
        $actor = $this->adminWith('view_users');
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $otherRole = Role::create(['name' => 'other', 'guard_name' => 'admin']);

        $target = Admin::factory()->create(['name' => '王小明', 'status' => Status::ACTIVE]);
        $target->assignRole($editorRole);

        $wrong = Admin::factory()->create(['name' => '王大偉', 'status' => Status::SUSPENDED]);
        $wrong->assignRole($editorRole);

        $other = Admin::factory()->create(['name' => '張三', 'status' => Status::ACTIVE]);
        $other->assignRole($otherRole);

        // WHEN  帶 keyword=王&status=active&roleId 篩選
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/admin?keyword=王&status=active&roleId={$editorRole->id}");

        // THEN  只回傳同時符合三條件的後台人員
        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame($target->id, $items[0]['id']);
    }

    /** perPage=25 時每頁最多 25 筆 */
    public function testRespectsPerPage(): void
    {
        // GIVEN 有 view_users 權限的管理員，及 30 筆後台人員
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        Admin::factory()->count(30)->create()->each(fn ($a) => $a->assignRole($staffRole));

        // WHEN  帶 perPage=25
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin?perPage=25');

        // THEN  perPage 為 25
        $response->assertOk()
            ->assertJsonPath('data.perPage', 25);
        $this->assertCount(25, $response->json('data.items'));
    }

    /** perPage 傳入不合法值時回傳 422，errors.perPage 含錯誤訊息 */
    public function testRejectsInvalidPerPage(): void
    {
        // GIVEN 有 view_users 權限的管理員
        $actor = $this->adminWith('view_users');

        // WHEN  傳入 perPage=100
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin?perPage=100');

        // THEN  回傳 422，errors.perPage 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['perPage']);
    }

    /** 無符合篩選條件時回傳空 items */
    public function testReturnsEmptyWhenNoMatch(): void
    {
        // GIVEN 有 view_users 權限的管理員，無其他後台人員
        $actor = $this->adminWith('view_users');

        // WHEN  帶不可能符合的關鍵字
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin?keyword=ZZZNOMATCH');

        // THEN  回傳 200，items 為空
        $response->assertOk()
            ->assertJsonPath('data.items', []);
    }

    /** 超級管理員不出現在列表 */
    public function testExcludesSuperAdmin(): void
    {
        // GIVEN 有 view_users 權限的管理員，及一筆超級管理員
        $actor = $this->adminWith('view_users');
        $staffRole = Role::create(['name' => 'staff_role', 'guard_name' => 'admin']);
        $superAdmin = Admin::factory()->superAdmin()->create();
        $superAdmin->assignRole($staffRole);

        // WHEN  發送 GET /admin-api/admin
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin');

        // THEN  回傳 200，items 不含該超級管理員
        $response->assertOk();
        $ids = collect($response->json('data.items'))->pluck('id');
        $this->assertNotContains($superAdmin->id, $ids);
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/admin
        $response = $this->getJson('/admin-api/admin');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 view_users 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送 GET /admin-api/admin
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/admin');

        // THEN  回傳 403
        $response->assertForbidden();
    }

    private function adminPermission(string $name): Permission
    {
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
    }

    private function adminWith(string ...$permissions): Admin
    {
        foreach ($permissions as $p) {
            $this->adminPermission($p);
        }
        $role = Role::create(['name' => 'actor_role', 'guard_name' => 'admin']);
        $role->givePermissionTo($permissions);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }

    private function adminWithNoPermission(): Admin
    {
        $role = Role::create(['name' => 'empty_role', 'guard_name' => 'admin']);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }
}
