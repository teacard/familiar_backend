<?php

namespace Tests\Feature\AdminApi\Product;

use App\Enums\Media\CollectionName;
use App\Enums\Product\Status;
use App\Enums\TemporaryMedia\SystemName;
use App\Models\Admin;
use App\Models\Item;
use App\Models\Product;
use App\Models\ProductType;
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

    /** 成功更新基本欄位 */
    public function testUpdatesBasicFields(): void
    {
        // GIVEN 有 edit_products 權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward(name: '舊名稱');
        $newType = ProductType::factory()->create();

        // WHEN  更新 name/productTypeId/amount/status
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, [
                'name' => '新名稱',
                'productTypeId' => $newType->id,
                'amount' => 999,
                'status' => Status::PUBLISHED->value,
            ]));

        // THEN  回傳 200，欄位已更新
        $response->assertOk()->assertJsonPath('data', []);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => '新名稱',
            'product_type_id' => $newType->id,
            'amount' => 999,
            'status' => Status::PUBLISHED->value,
        ]);
    }

    /** productRewards 為整批覆蓋，未帶入的舊明細會被刪除 */
    public function testOverwritesProductRewards(): void
    {
        // GIVEN 有權限的管理員，及一筆帶兩筆獎勵明細的商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $product->productRewards()->create(['item_id' => $item1->id, 'quantity' => 1]);
        $product->productRewards()->create(['item_id' => $item2->id, 'quantity' => 2]);
        $newItem = Item::factory()->create();

        // WHEN  更新請求只帶入一筆新的獎勵明細
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, [
                'productRewards' => [['itemId' => $newItem->id, 'quantity' => 9]],
            ]));

        // THEN  回傳 200，舊明細被刪除，只剩新明細
        $response->assertOk();
        $this->assertDatabaseMissing('product_rewards', ['product_id' => $product->id, 'item_id' => $item1->id]);
        $this->assertDatabaseMissing('product_rewards', ['product_id' => $product->id, 'item_id' => $item2->id]);
        $this->assertDatabaseHas('product_rewards', ['product_id' => $product->id, 'item_id' => $newItem->id, 'quantity' => 9]);
    }

    /** mediaId 帶入新上傳的暫存媒體時，圖片被替換 */
    public function testReplacesImageWithNewTemporaryMedia(): void
    {
        // GIVEN 有權限的管理員，及一筆已有主圖的商品，另有一筆新上傳的暫存媒體
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();
        $newMedia = $this->temporaryMedia();

        // WHEN  mediaId 帶入新的暫存媒體 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, ['mediaId' => $newMedia->id]));

        // THEN  回傳 200，商品主圖已替換為新媒體
        $response->assertOk();
        $this->assertDatabaseHas('media', [
            'id' => $newMedia->id,
            'model_id' => $product->id,
            'collection_name' => CollectionName::PRODUCT->value,
        ]);
        $this->assertCount(1, $product->fresh()->getMedia(CollectionName::PRODUCT->value));
    }

    /** mediaId 帶入本商品目前已掛載的主圖 id 時（未換圖），驗證通過且主圖維持不變 */
    public function testKeepsExistingImageWhenMediaIdIsOwnCurrentImage(): void
    {
        // GIVEN 有權限的管理員，及一筆已有主圖的商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();
        $currentMediaId = $product->getFirstMedia(CollectionName::PRODUCT->value)->id;

        // WHEN  mediaId 帶入本商品目前已掛載的主圖 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, ['mediaId' => $currentMediaId]));

        // THEN  回傳 200，主圖仍是同一筆媒體
        $response->assertOk();
        $this->assertCount(1, $product->fresh()->getMedia(CollectionName::PRODUCT->value));
        $this->assertSame($currentMediaId, $product->fresh()->getFirstMedia(CollectionName::PRODUCT->value)->id);
    }

    /** mediaId 帶入另一個商品已掛載的主圖 id 時回傳 422 */
    public function testRejectsMediaIdBelongingToAnotherProduct(): void
    {
        // GIVEN 有權限的管理員，及兩筆各自有主圖的商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();
        $otherProduct = $this->productWithImageAndReward();
        $otherMediaId = $otherProduct->getFirstMedia(CollectionName::PRODUCT->value)->id;

        // WHEN  mediaId 帶入另一個商品的主圖 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, ['mediaId' => $otherMediaId]));

        // THEN  回傳 422，errors.mediaId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mediaId']);
    }

    /** 未提供 name 時回傳 422 */
    public function testRejectsMissingName(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  body 不含 name
        $payload = $this->payload($product);
        unset($payload['name']);
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $payload);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** name 超過 50 字元時回傳 422 */
    public function testRejectsNameExceeding50Chars(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  name 為 51 字元
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, [
                'name' => str_repeat('a', 51),
            ]));

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** productTypeId 不存在於 product_types 時回傳 422 */
    public function testRejectsNonexistentProductTypeId(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  productTypeId 為不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, [
                'productTypeId' => 999999,
            ]));

        // THEN  回傳 422，errors.productTypeId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productTypeId']);
    }

    /** amount 為 0 時回傳 422 */
    public function testRejectsZeroAmount(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  amount 為 0
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, ['amount' => 0]));

        // THEN  回傳 422，errors.amount 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    /** amount 為負數時回傳 422 */
    public function testRejectsNegativeAmount(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  amount 為負數
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, ['amount' => -1]));

        // THEN  回傳 422，errors.amount 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    /** status 為非法值時回傳 422 */
    public function testRejectsInvalidStatus(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  status 為不合法的值
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, ['status' => 'unknown']));

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** 未帶 productRewards 時回傳 422 */
    public function testRejectsMissingProductRewards(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  body 不含 productRewards
        $payload = $this->payload($product);
        unset($payload['productRewards']);
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $payload);

        // THEN  回傳 422，errors.productRewards 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards']);
    }

    /** productRewards 為空陣列時回傳 422 */
    public function testRejectsEmptyProductRewards(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  productRewards 為空陣列
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, ['productRewards' => []]));

        // THEN  回傳 422，errors.productRewards 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards']);
    }

    /** productRewards 任一筆缺少 itemId 時回傳 422 */
    public function testRejectsRewardMissingItemId(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  productRewards 該筆缺少 itemId
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, [
                'productRewards' => [['quantity' => 1]],
            ]));

        // THEN  回傳 422，errors 包含 productRewards.0.itemId 的驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards.0.itemId']);
    }

    /** productRewards 任一筆的 itemId 指向不存在於 items 表的 id 時回傳 422 */
    public function testRejectsRewardWithNonexistentItemId(): void
    {
        // GIVEN 有權限的管理員，及一筆商品
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();

        // WHEN  productRewards 該筆 itemId 為不存在的道具 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, [
                'productRewards' => [['itemId' => 999999, 'quantity' => 1]],
            ]));

        // THEN  回傳 422，errors 包含 productRewards.0.itemId 的驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards.0.itemId']);
    }

    /** productRewards 中出現重複 itemId 時回傳 422 */
    public function testRejectsDuplicateItemIdInRewards(): void
    {
        // GIVEN 有權限的管理員，及一筆商品，及一個道具
        $actor = $this->adminWith('edit_products');
        $product = $this->productWithImageAndReward();
        $item = Item::factory()->create();

        // WHEN  productRewards 帶入兩筆相同的 itemId
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product, [
                'productRewards' => [
                    ['itemId' => $item->id, 'quantity' => 1],
                    ['itemId' => $item->id, 'quantity' => 2],
                ],
            ]));

        // THEN  回傳 422，errors 包含 productRewards.*.itemId 的驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards.0.itemId', 'productRewards.1.itemId']);
    }

    /** 查無商品時回傳 404 */
    public function testReturns404WhenNotFound(): void
    {
        // GIVEN 有權限的管理員，及一筆合法（存在）的暫存媒體供驗證通過
        $actor = $this->adminWith('edit_products');
        $productType = ProductType::factory()->create();
        $item = Item::factory()->create();
        $media = $this->temporaryMedia();

        // WHEN  更新不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->putJson('/admin-api/products/999999', [
                'name' => '新名稱',
                'productTypeId' => $productType->id,
                'amount' => 100,
                'status' => Status::UNPUBLISHED->value,
                'mediaId' => $media->id,
                'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
            ]);

        // THEN  回傳 404
        $response->assertNotFound();
    }

    /** 缺少 edit_products 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆商品
        $actor = $this->adminWithNoPermission();
        $product = $this->productWithImageAndReward();

        // WHEN  發送更新請求
        $response = $this->actingAs($actor, 'admin')
            ->putJson("/admin-api/products/{$product->id}", $this->payload($product));

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** 建立一筆已有主圖與一筆獎勵明細的商品 */
    private function productWithImageAndReward(?string $name = null): Product
    {
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $product = Product::factory()->create($name ? ['name' => $name] : []);
        $product->addMedia(UploadedFile::fake()->image('product.png'))
            ->toMediaCollection(CollectionName::PRODUCT->value);
        $item = Item::factory()->create();
        $product->productRewards()->create(['item_id' => $item->id, 'quantity' => 1]);

        return $product;
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
    private function payload(Product $product, array $overrides = []): array
    {
        $item = Item::factory()->create();

        return array_merge([
            'name' => $product->name,
            'productTypeId' => $product->product_type_id,
            'amount' => $product->amount,
            'status' => $product->status->value,
            'mediaId' => $product->getFirstMedia(CollectionName::PRODUCT->value)?->id,
            'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
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
