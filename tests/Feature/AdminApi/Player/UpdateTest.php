<?php

namespace Tests\Feature\AdminApi\Player;

use App\Models\Admin;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** 不更換密碼時成功更新，密碼保持不變 */
    public function testSucceedsWithoutChangingPassword(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及一筆玩家
        $actor = $this->adminWith('edit_players');
        $target = Player::factory()->create(['name' => '舊名字']);
        $originalHash = $target->password;

        // WHEN  更新 name 不帶 password
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => '新名字',
                'email' => $target->email,
                'status' => 'active',
            ]);

        // THEN  回傳 200，name 已更新、密碼 hash 不變
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseHas('players', ['id' => $target->id, 'name' => '新名字']);
        $this->assertSame($originalHash, $target->fresh()->password);
    }

    /** 帶有效新密碼時成功更新並重新雜湊 */
    public function testSucceedsWithNewPassword(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及一筆玩家
        $actor = $this->adminWith('edit_players');
        $target = Player::factory()->create();
        $originalHash = $target->password;

        // WHEN  帶新密碼更新
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'status' => 'active',
                'password' => 'newpassword123',
                'passwordConfirmation' => 'newpassword123',
            ]);

        // THEN  回傳 200，密碼 hash 已改變
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertNotSame($originalHash, $target->fresh()->password);
    }

    /** 透過編輯切換狀態 active -> suspended */
    public function testTogglesStatus(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及一筆啟用玩家
        $actor = $this->adminWith('edit_players');
        $target = Player::factory()->create();

        // WHEN  將狀態改為 suspended
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'status' => 'suspended',
            ]);

        // THEN  回傳 200，狀態變更為 suspended
        $response->assertOk();
        $this->assertDatabaseHas('players', ['id' => $target->id, 'status' => 'suspended']);
    }

    /** email 與其他玩家重複時回傳 422 */
    public function testRejectsDuplicateEmail(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及兩筆玩家
        $actor = $this->adminWith('edit_players');
        $other = Player::factory()->create(['email' => 'taken@example.com']);
        $target = Player::factory()->create();

        // WHEN  將 target 的 email 改成 other 的 email
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => $target->name,
                'email' => $other->email,
                'status' => 'active',
            ]);

        // THEN  回傳 422，errors.email
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** email 維持自身原值時允許更新 */
    public function testAllowsSameEmail(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及一筆玩家
        $actor = $this->adminWith('edit_players');
        $target = Player::factory()->create(['email' => 'self@example.com']);

        // WHEN  email 維持自身原值
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => '改名',
                'email' => 'self@example.com',
                'status' => 'active',
            ]);

        // THEN  回傳 200
        $response->assertOk();
    }

    /** phone 與其他玩家重複時回傳 422 */
    public function testRejectsDuplicatePhone(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及兩筆玩家
        $actor = $this->adminWith('edit_players');
        $other = Player::factory()->create(['phone' => '0911222333']);
        $target = Player::factory()->create(['phone' => '0900000000']);

        // WHEN  將 target 的 phone 改成 other 的 phone
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'phone' => $other->phone,
                'status' => 'active',
            ]);

        // THEN  回傳 422，errors.phone
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    /** phone 維持自身原值時允許更新 */
    public function testAllowsSamePhone(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及一筆玩家
        $actor = $this->adminWith('edit_players');
        $target = Player::factory()->create(['phone' => '0912345678']);

        // WHEN  phone 維持自身原值
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'phone' => '0912345678',
                'status' => 'active',
            ]);

        // THEN  回傳 200
        $response->assertOk();
    }

    /** 請求帶入 player_number 應被忽略，維持原值 */
    public function testIgnoresPlayerNumber(): void
    {
        // GIVEN 有 edit_players 權限的管理員，及一筆玩家
        $actor = $this->adminWith('edit_players');
        $target = Player::factory()->create();
        $originalNumber = $target->player_number;

        // WHEN  更新時嘗試帶入 player_number
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'status' => 'active',
                'player_number' => 'PL00000001',
            ]);

        // THEN  回傳 200，player_number 維持原值
        $response->assertOk();
        $this->assertSame($originalNumber, $target->fresh()->player_number);
    }

    /** 更新不存在的玩家回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有 edit_players 權限的管理員
        $actor = $this->adminWith('edit_players');

        // WHEN  更新不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson('/admin-api/players/99999', [
                'name' => '張三',
                'email' => 'test@example.com',
                'status' => 'active',
            ]);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 edit_players 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆玩家
        $actor = $this->adminWithNoPermission();
        $target = Player::factory()->create();

        // WHEN  發送更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/players/{$target->id}", [
                'name' => '張三',
                'email' => 'test@example.com',
                'status' => 'active',
            ]);

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
