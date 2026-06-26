<?php

namespace Tests\Feature\AdminApi\Player;

use App\Models\Admin;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 有 view_players 權限時可取得玩家分頁列表 */
    public function testReturnsPaginatedPlayers(): void
    {
        // GIVEN 有 view_players 權限的管理員，及 3 筆玩家
        $actor = $this->adminWith('view_players');
        Player::factory()->count(3)->create();

        // WHEN  取得列表
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players');

        // THEN  回傳 200，data.items 含 3 筆，且帶分頁欄位
        $response->assertOk()
            ->assertJsonCount(3, 'data.items')
            ->assertJsonPath('data.currentPage', 1);
    }

    /** 關鍵字可比對名稱、玩家 ID 與 Email */
    public function testFiltersByKeyword(): void
    {
        // GIVEN 三筆可區分的玩家
        $actor = $this->adminWith('view_players');
        $target = Player::factory()->create(['name' => '生物蒐集家', 'email' => 'collector@example.com']);
        Player::factory()->create(['name' => '路人甲', 'email' => 'a@example.com']);
        Player::factory()->create(['name' => '路人乙', 'email' => 'b@example.com']);

        // WHEN  以名稱關鍵字搜尋
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players?keyword=蒐集');

        // THEN  僅回傳符合的玩家
        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $target->id);
    }

    /** 狀態篩選僅回傳指定狀態的玩家 */
    public function testFiltersByStatus(): void
    {
        // GIVEN 啟用 2 筆、停用 1 筆
        $actor = $this->adminWith('view_players');
        Player::factory()->count(2)->create();
        $suspended = Player::factory()->suspended()->create();

        // WHEN  篩選 suspended
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players?status=suspended');

        // THEN  僅回傳停用的 1 筆
        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $suspended->id);
    }

    /** 分頁參數 perPage 與 page 正確生效 */
    public function testPaginates(): void
    {
        // GIVEN 15 筆玩家
        $actor = $this->adminWith('view_players');
        Player::factory()->count(15)->create();

        // WHEN  每頁 10 筆、取第 2 頁
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players?perPage=10&page=2');

        // THEN  第 2 頁回傳剩餘 5 筆
        $response->assertOk()
            ->assertJsonCount(5, 'data.items')
            ->assertJsonPath('data.currentPage', 2)
            ->assertJsonPath('data.perPage', 10);
    }

    /** 無資料時回傳空列表 */
    public function testReturnsEmptyWhenNoPlayers(): void
    {
        // GIVEN 沒有任何玩家
        $actor = $this->adminWith('view_players');

        // WHEN  取得列表
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players');

        // THEN  回傳 200，items 為空
        $response->assertOk()
            ->assertJsonCount(0, 'data.items');
    }

    /** 關鍵字含 LIKE 特殊字元時正確跳脫，不會誤匹配 */
    public function testEscapesKeywordSpecialChars(): void
    {
        // GIVEN 一筆名稱不含 % 的玩家
        $actor = $this->adminWith('view_players');
        Player::factory()->create(['name' => '正常玩家']);

        // WHEN  以字面 % 搜尋
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players?keyword=%25');

        // THEN  回傳 200 且不誤匹配（0 筆）
        $response->assertOk()
            ->assertJsonCount(0, 'data.items');
    }

    /** 缺少 view_players 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  取得列表
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players');

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** 未登入時回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未帶 Token
        // WHEN  取得列表
        $response = $this->getJson('/admin-api/players');

        // THEN  回傳 401
        $response->assertUnauthorized();
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
