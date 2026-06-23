<?php

namespace Tests\Feature\AdminApi\Player;

use App\Models\Admin;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** 合法資料可成功建立玩家，狀態依輸入、player_number 自動產生且符合格式 */
    public function testCreatesPlayer(): void
    {
        // GIVEN 有 create_players 權限的管理員
        $actor = $this->adminWith('create_players');

        // WHEN  送出合法建立請求（狀態 active）
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '生物蒐集家',
                'email' => 'collector@example.com',
                'phone' => '0912345678',
                'status' => 'active',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 200，玩家已建立、狀態 active，player_number 形如 PL+8 位數字
        $response->assertOk()
            ->assertJsonPath('data', []);
        $this->assertDatabaseHas('players', ['email' => 'collector@example.com', 'status' => 'active']);
        $player = Player::where('email', 'collector@example.com')->first();
        $this->assertMatchesRegularExpression('/^PL\d{8}$/', $player->player_number);
    }

    /** 建立時依請求帶入的狀態寫入（suspended） */
    public function testCreatesPlayerWithSuspendedStatus(): void
    {
        // GIVEN 有 create_players 權限的管理員
        $actor = $this->adminWith('create_players');

        // WHEN  送出狀態為 suspended 的建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '停用玩家',
                'email' => 'suspended@example.com',
                'status' => 'suspended',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 200，狀態為 suspended
        $response->assertOk();
        $this->assertDatabaseHas('players', ['email' => 'suspended@example.com', 'status' => 'suspended']);
    }

    /** 未填 phone 時仍可建立（phone 為選填） */
    public function testCreatesPlayerWithoutPhone(): void
    {
        // GIVEN 有 create_players 權限的管理員
        $actor = $this->adminWith('create_players');

        // WHEN  不帶 phone 建立
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '無手機玩家',
                'email' => 'nophone@example.com',
                'status' => 'active',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 200，玩家已建立且 phone 為 null
        $response->assertOk();
        $this->assertDatabaseHas('players', ['email' => 'nophone@example.com', 'phone' => null]);
    }

    /** 未提供 status 時回傳 422 */
    public function testRejectsWhenStatusNotProvided(): void
    {
        // GIVEN 有 create_players 權限的管理員
        $actor = $this->adminWith('create_players');

        // WHEN  body 不含 status
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '張三',
                'email' => 'test@example.com',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 422，errors.status
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** status 不在 enum 範圍時回傳 422 */
    public function testRejectsInvalidStatus(): void
    {
        // GIVEN 有 create_players 權限的管理員
        $actor = $this->adminWith('create_players');

        // WHEN  status 傳入不合法值
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '張三',
                'email' => 'test@example.com',
                'status' => 'unknown',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 422，errors.status
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** email 與現有玩家重複時回傳 422 */
    public function testRejectsDuplicateEmail(): void
    {
        // GIVEN 有 create_players 權限的管理員，且該 email 已存在
        $actor = $this->adminWith('create_players');
        Player::factory()->create(['email' => 'dup@example.com']);

        // WHEN  以相同 email 建立
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '張三',
                'email' => 'dup@example.com',
                'status' => 'active',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 422，errors.email
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** phone 與現有玩家重複時回傳 422 */
    public function testRejectsDuplicatePhone(): void
    {
        // GIVEN 有 create_players 權限的管理員，且該 phone 已存在
        $actor = $this->adminWith('create_players');
        Player::factory()->create(['phone' => '0911222333']);

        // WHEN  以相同 phone 建立
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '張三',
                'email' => 'new@example.com',
                'phone' => '0911222333',
                'status' => 'active',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
            ]);

        // THEN  回傳 422，errors.phone
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    /** 密碼少於 8 字元時回傳 422 */
    public function testRejectsShortPassword(): void
    {
        // GIVEN 有 create_players 權限的管理員
        $actor = $this->adminWith('create_players');

        // WHEN  password 只有 7 字元
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '張三',
                'email' => 'test@example.com',
                'status' => 'active',
                'password' => '1234567',
                'passwordConfirmation' => '1234567',
            ]);

        // THEN  回傳 422，errors.password
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /** 密碼與確認密碼不一致時回傳 422 */
    public function testRejectsMismatchedPasswords(): void
    {
        // GIVEN 有 create_players 權限的管理員
        $actor = $this->adminWith('create_players');

        // WHEN  password 與 passwordConfirmation 不同
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '張三',
                'email' => 'test@example.com',
                'status' => 'active',
                'password' => 'password123',
                'passwordConfirmation' => 'different123',
            ]);

        // THEN  回傳 422，errors.password
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /** 缺少 create_players 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送建立請求（帶合法欄位以測到權限檢查）
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/players', [
                'name' => '張三',
                'email' => 'test@example.com',
                'status' => 'active',
                'password' => 'password123',
                'passwordConfirmation' => 'password123',
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
