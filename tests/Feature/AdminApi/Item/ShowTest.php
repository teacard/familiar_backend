<?php

namespace Tests\Feature\AdminApi\Item;

use App\Enums\Media\CollectionName;
use App\Models\Admin;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /** 可取得道具詳情，含 name、status、image */
    public function testReturnsItemDetail(): void
    {
        // GIVEN 有 view_items 權限的管理員，及一筆含圖片的道具
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWith('view_items');
        $item = Item::factory()->create(['name' => '金幣']);
        $item->addMedia(UploadedFile::fake()->image('item.png'))
            ->toMediaCollection(CollectionName::ITEM->value);

        // WHEN  發送 GET /admin-api/items/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/items/{$item->id}");

        // THEN  回傳 200，含 name、status、image
        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['name', 'status', 'image' => ['id', 'url']],
            ])
            ->assertJsonPath('data.name', '金幣');
    }

    /** 查無道具時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('view_items');

        // WHEN  查詢不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items/999999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 view_items 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆含圖片的道具
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $actor = $this->adminWithNoPermission();
        $item = Item::factory()->create();
        $item->addMedia(UploadedFile::fake()->image('item.png'))
            ->toMediaCollection(CollectionName::ITEM->value);

        // WHEN  發送 GET /admin-api/items/{id}
        $response = $this->actingAs($actor, 'admin')
            ->getJson("/admin-api/items/{$item->id}");

        // THEN  回傳 403
        $response->assertForbidden();
    }

    private function adminWith(string ...$permissions): Admin
    {
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'admin']);
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
