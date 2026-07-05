<?php

namespace Tests\Feature\AdminApi\Item;

use App\Enums\ApiCode;
use App\Enums\Media\CollectionName;
use App\Models\Admin;
use App\Models\Item;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** 未被使用的道具可成功刪除，其圖片媒體一併清除 */
    public function testDeletesUnusedItem(): void
    {
        // GIVEN 有 delete_items 權限的管理員，及一個無人使用、含圖片的道具
        $actor = $this->adminWith('delete_items');
        $item = $this->itemWithImage();
        $mediaId = $item->getFirstMedia(CollectionName::ITEM->value)->id;

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/items/{$item->id}");

        // THEN  回傳 200，道具與其圖片媒體皆已從資料庫移除
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    }

    /** 道具仍被商品獎勵明細使用時，回傳 422 並帶 ITEM_IN_USE，道具未被刪除 */
    public function testRejectsDeletingItemInUse(): void
    {
        // GIVEN 有權限的管理員，及一個被商品獎勵明細使用的道具
        $actor = $this->adminWith('delete_items');
        $item = $this->itemWithImage();
        $product = Product::factory()->create();
        $product->productRewards()->create(['item_id' => $item->id, 'quantity' => 1]);

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/items/{$item->id}");

        // THEN  回傳 422，apiCode 為 ITEM_IN_USE，道具仍存在
        $response->assertUnprocessable()
            ->assertJsonPath('apiCode', ApiCode::ITEM_IN_USE->value);
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    /** 查無道具時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('delete_items');

        // WHEN  刪除不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson('/admin-api/items/999999');

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token，及一筆道具
        $item = $this->itemWithImage();

        // WHEN  發送 DELETE /admin-api/items/{id}
        $response = $this->deleteJson("/admin-api/items/{$item->id}");

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 delete_items 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆道具
        $actor = $this->adminWithNoPermission();
        $item = $this->itemWithImage();

        // WHEN  發送刪除請求
        $response = $this->actingAs($actor, 'admin')
            ->deleteJson("/admin-api/items/{$item->id}");

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** @param array<string, mixed> $attributes */
    private function itemWithImage(array $attributes = []): Item
    {
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $item = Item::factory()->create($attributes);
        $item->addMedia(UploadedFile::fake()->image('item.png'))
            ->toMediaCollection(CollectionName::ITEM->value);

        return $item;
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
