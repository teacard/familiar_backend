<?php

namespace Tests\Feature\AdminApi\Player;

use App\Models\Admin;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 有 view_players 權限時可取得玩家詳情，僅含編輯表單欄位且不含密碼 */
    public function testReturnsPlayer(): void
    {
        // GIVEN 有 view_players 權限的管理員，及一筆玩家
        $actor = $this->adminWith('view_players');
        $target = Player::factory()->create([
            'name' => '生物蒐集家',
            'email' => 'collector@example.com',
            'phone' => '0912345678',
        ]);

        // WHEN  取得詳情
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/players/{$target->id}");

        // THEN  回傳 200，含 name/email/phone/status，不含密碼與非表單欄位
        $response->assertOk()
            ->assertJsonPath('data.name', '生物蒐集家')
            ->assertJsonPath('data.email', 'collector@example.com')
            ->assertJsonPath('data.phone', '0912345678')
            ->assertJsonPath('data.status', 'active');
        $data = $response->json('data');
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('playerNumber', $data);
        $this->assertArrayNotHasKey('createdDate', $data);
    }

    /** 查無玩家時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有 view_players 權限的管理員
        $actor = $this->adminWith('view_players');

        // WHEN  以不存在的 id 取得詳情
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/players/99999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 view_players 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆玩家
        $actor = $this->adminWithNoPermission();
        $target = Player::factory()->create();

        // WHEN  取得詳情
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/players/{$target->id}");

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
