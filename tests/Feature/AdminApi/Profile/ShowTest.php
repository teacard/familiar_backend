<?php

namespace Tests\Feature\AdminApi\Profile;

use App\Enums\Auth\Guard;
use App\Enums\Media\CollectionName;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得個人資料，含 name、photo、permissions，且 name 為登入者本人 */
    public function testReturnsOwnProfile(): void
    {
        // GIVEN 已登入後台人員
        $actor = $this->adminWith('view_users');

        // WHEN  發送 GET /admin-api/profile
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/profile');

        // THEN  回傳 200，含三欄，name 為本人
        $response->assertOk()
            ->assertJsonStructure(['data' => ['name', 'photo', 'permissions']])
            ->assertJsonPath('data.name', $actor->name);
    }

    /** permissions 為字串陣列，涵蓋登入者全部權限（含角色帶來與直接指派），且去重 */
    public function testPermissionsAreUniqueNameStrings(): void
    {
        // GIVEN 登入者透過角色擁有 view_users、view_roles，並「直接指派」一個與角色重複的 view_users
        $actor = $this->adminWith('view_users', 'view_roles');
        $actor->givePermissionTo('view_users'); // 與角色權限重複，用於驗證去重

        // WHEN  發送 GET /admin-api/profile
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/profile');

        // THEN  permissions 為字串陣列，含 view_users、view_roles，且無重複
        $response->assertOk();
        $permissions = $response->json('data.permissions');

        $this->assertIsArray($permissions);
        $this->assertContainsOnly('string', $permissions);
        $this->assertContains('view_users', $permissions);
        $this->assertContains('view_roles', $permissions);
        $this->assertSame(array_values(array_unique($permissions)), $permissions);
    }

    /** 無任何權限時 permissions 為空陣列 */
    public function testPermissionsEmptyWhenNoPermission(): void
    {
        // GIVEN 有角色但無任何權限的登入者
        $actor = $this->adminWith();

        // WHEN  發送 GET /admin-api/profile
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/profile');

        // THEN  permissions 為空陣列
        $response->assertOk()
            ->assertJsonPath('data.permissions', []);
    }

    /** 無頭像時 photo 回預設頭像 fallback URL（含 profile_icon.svg）的非空字串 */
    public function testPhotoReturnsFallbackWhenNoAvatar(): void
    {
        // GIVEN 已登入後台人員（無任何頭像）
        $actor = $this->adminWith();

        // WHEN  發送 GET /admin-api/profile
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/profile');

        // THEN  photo 為非空字串且指向預設頭像
        $response->assertOk();
        $photo = $response->json('data.photo');

        $this->assertIsString($photo);
        $this->assertNotEmpty($photo);
        $this->assertStringContainsString('profile_icon.svg', $photo);
    }

    /** 有頭像時 photo 回該媒體的完整 URL（非 fallback 預設頭像） */
    public function testPhotoReturnsAvatarMediaUrlWhenPresent(): void
    {
        // GIVEN 已登入後台人員，且已上傳一張頭像（媒體 disk 以 fake 模擬）
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWith();
        $actor->addMedia(UploadedFile::fake()->create('me.png', 10, 'image/png'))
            ->toMediaCollection(CollectionName::ADMIN->value);

        // WHEN  發送 GET /admin-api/profile
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/profile');

        // THEN  photo 指向上傳的頭像（含檔名 me），且非預設頭像
        $response->assertOk();
        $photo = $response->json('data.photo');

        $this->assertIsString($photo);
        $this->assertStringContainsString('me.png', $photo);
        $this->assertStringNotContainsString('profile_icon.svg', $photo);
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/profile
        $response = $this->getJson('/admin-api/profile');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 建立一個具角色與指定權限的已登入後台人員 */
    private function adminWith(string ...$permissions): Admin
    {
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => Guard::ADMIN->value]);
        }
        $role = Role::create(['name' => 'actor_role', 'guard_name' => Guard::ADMIN->value]);
        $role->givePermissionTo($permissions);
        $admin = Admin::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }
}
