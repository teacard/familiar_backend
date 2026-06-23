<?php

namespace Tests\Feature\AdminApi\Player;

use App\Enums\ApiCode;
use App\Models\Admin;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** 停用狀態的玩家可成功軟刪除 */
    public function testDeletesSuspendedPlayer(): void
    {
        // GIVEN 有 delete_players 權限的管理員，及一筆停用玩家
        $actor = $this->adminWith('delete_players');
        $target = Player::factory()->suspended()->create();

        // WHEN  發送 DELETE
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/players/{$target->id}");

        // THEN  回傳 200，玩家被軟刪除（deleted_at 寫入）
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertSoftDeleted('players', ['id' => $target->id]);
    }

    /** 軟刪除後，列表不再顯示該玩家 */
    public function testDeletedPlayerDisappearsFromList(): void
    {
        // GIVEN 有 delete_players + view_players 權限的管理員，及一筆停用玩家
        $actor = $this->adminWith('delete_players', 'view_players');
        $target = Player::factory()->suspended()->create();

        // WHEN  刪除後查詢列表
        $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/players/{$target->id}");

        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players');

        // THEN  已刪除的玩家不出現在列表
        $ids = collect($response->json('data.items'))->pluck('id');
        $this->assertNotContains($target->id, $ids);
    }

    /** 啟用中的玩家不可刪除，回傳 422 與對應 apiCode */
    public function testRejectsDeletingActivePlayer(): void
    {
        // GIVEN 有 delete_players 權限的管理員，及一筆啟用玩家
        $actor = $this->adminWith('delete_players');
        $target = Player::factory()->create();

        // WHEN  嘗試刪除啟用玩家
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/players/{$target->id}");

        // THEN  回傳 422、apiCode 為 PLAYER_NOT_SUSPENDED，玩家未被刪除
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::PLAYER_NOT_SUSPENDED->value);
        $this->assertDatabaseHas('players', ['id' => $target->id, 'deleted_at' => null]);
    }

    /** 刪除已被刪除的玩家回傳 404 */
    public function testReturns404ForAlreadyDeletedPlayer(): void
    {
        // GIVEN 有 delete_players 權限的管理員，及一筆已被刪除的玩家
        $actor = $this->adminWith('delete_players');
        $target = Player::factory()->suspended()->create();
        $target->delete();

        // WHEN  再次刪除
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/players/{$target->id}");

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 刪除不存在的玩家回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有 delete_players 權限的管理員
        $actor = $this->adminWith('delete_players');

        // WHEN  刪除不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson('/admin-api/players/99999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 delete_players 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆停用玩家
        $actor = $this->adminWithNoPermission();
        $target = Player::factory()->suspended()->create();

        // WHEN  發送 DELETE
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/players/{$target->id}");

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
