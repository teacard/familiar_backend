<?php

namespace Tests\Feature\AdminApi\Item;

use App\Enums\Item\Status;
use App\Enums\Media\CollectionName;
use App\Enums\TemporaryMedia\SystemName;
use App\Models\Admin;
use App\Models\Item;
use App\Models\TemporaryMedia;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** 成功更新道具名稱 */
    public function testUpdatesName(): void
    {
        // GIVEN 有 edit_items 權限的管理員，及一筆含圖片的道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage(['name' => '舊名稱']);

        // WHEN  發送更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item, ['name' => '新名稱']));

        // THEN  回傳 200，name 已更新
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseHas('items', ['id' => $item->id, 'name' => '新名稱']);
    }

    /** 成功更新道具啟用狀態 */
    public function testUpdatesStatus(): void
    {
        // GIVEN 有 edit_items 權限的管理員，及一筆啟用中的道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage(['status' => Status::ACTIVE]);

        // WHEN  發送更新請求，status 改為停用
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item, ['status' => Status::DISABLED->value]));

        // THEN  回傳 200，status 已更新為停用
        $response->assertOk();
        $this->assertSame(Status::DISABLED, $item->fresh()->status);
    }

    /** 未提供 status 時回傳 422 */
    public function testRejectsMissingStatus(): void
    {
        // GIVEN 有權限的管理員，及一筆道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage();

        // WHEN  body 不含 status
        $payload = $this->payload($item);
        unset($payload['status']);
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $payload);

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** status 為非法值時回傳 422 */
    public function testRejectsInvalidStatus(): void
    {
        // GIVEN 有權限的管理員，及一筆道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage();

        // WHEN  status 為不合法的值
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item, ['status' => 'unknown']));

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** mediaId 帶入新上傳的暫存媒體時，圖片被替換 */
    public function testReplacesImageWithNewTemporaryMedia(): void
    {
        // GIVEN 有權限的管理員，及一筆已有圖片的道具，另有一筆新上傳的暫存媒體
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage();
        $newMedia = $this->temporaryMedia();

        // WHEN  mediaId 帶入新的暫存媒體 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item, ['mediaId' => $newMedia->id]));

        // THEN  回傳 200，道具圖片已替換為新媒體
        $response->assertOk();
        $this->assertDatabaseHas('media', [
            'id' => $newMedia->id,
            'model_id' => $item->id,
            'collection_name' => CollectionName::ITEM->value,
        ]);
        $this->assertCount(1, $item->fresh()->getMedia(CollectionName::ITEM->value));
    }

    /** mediaId 帶入本道具目前已掛載的圖片 id 時（未換圖），驗證通過且圖片維持不變 */
    public function testKeepsExistingImageWhenMediaIdIsOwnCurrentImage(): void
    {
        // GIVEN 有權限的管理員，及一筆已有圖片的道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage();
        $currentMediaId = $item->getFirstMedia(CollectionName::ITEM->value)->id;

        // WHEN  mediaId 帶入本道具目前已掛載的圖片 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item, ['mediaId' => $currentMediaId]));

        // THEN  回傳 200，圖片仍是同一筆媒體
        $response->assertOk();
        $this->assertCount(1, $item->fresh()->getMedia(CollectionName::ITEM->value));
        $this->assertSame($currentMediaId, $item->fresh()->getFirstMedia(CollectionName::ITEM->value)->id);
    }

    /** mediaId 帶入另一個道具已掛載的圖片 id 時回傳 422 */
    public function testRejectsMediaIdBelongingToAnotherItem(): void
    {
        // GIVEN 有權限的管理員，及兩筆各自有圖片的道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage();
        $otherItem = $this->itemWithImage();
        $otherMediaId = $otherItem->getFirstMedia(CollectionName::ITEM->value)->id;

        // WHEN  mediaId 帶入另一個道具的圖片 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item, ['mediaId' => $otherMediaId]));

        // THEN  回傳 422，errors.mediaId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mediaId']);
    }

    /** 未提供 name 時回傳 422 */
    public function testRejectsMissingName(): void
    {
        // GIVEN 有權限的管理員，及一筆道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage();

        // WHEN  body 不含 name
        $payload = $this->payload($item);
        unset($payload['name']);
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $payload);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** name 超過 10 字元時回傳 422 */
    public function testRejectsNameExceeding10Chars(): void
    {
        // GIVEN 有權限的管理員，及一筆道具
        $actor = $this->adminWith('edit_items');
        $item = $this->itemWithImage();

        // WHEN  name 為 11 字元
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item, ['name' => str_repeat('道', 11)]));

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** 查無道具時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員，及一筆合法（存在）的暫存媒體供驗證通過
        $actor = $this->adminWith('edit_items');
        $media = $this->temporaryMedia();

        // WHEN  更新不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson('/admin-api/items/999999', [
                'name' => '新名稱',
                'status' => Status::ACTIVE->value,
                'mediaId' => $media->id,
            ]);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 edit_items 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆道具
        $actor = $this->adminWithNoPermission();
        $item = $this->itemWithImage();

        // WHEN  發送更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/items/{$item->id}", $this->payload($item));

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token，及一筆道具
        $item = $this->itemWithImage();

        // WHEN  發送 PUT /admin-api/items/{id}
        $response = $this->putJson("/admin-api/items/{$item->id}", $this->payload($item));

        // THEN  回傳 401
        $response->assertUnauthorized();
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

    private function temporaryMedia(): Media
    {
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();

        return $owner->addMedia(UploadedFile::fake()->image('new.png'))
            ->toMediaCollection(CollectionName::TEMPORARY->value);
    }

    /** @param array<string, mixed> $overrides */
    private function payload(Item $item, array $overrides = []): array
    {
        return array_merge([
            'name' => $item->name,
            'status' => $item->status->value,
            'mediaId' => $item->getFirstMedia(CollectionName::ITEM->value)?->id,
        ], $overrides);
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
