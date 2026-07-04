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

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** 合法資料（含圖片、多筆不同道具的 productRewards）可成功建立商品 */
    public function testCreatesProductWithImageAndMultipleRewards(): void
    {
        // GIVEN 有 create_products 權限的管理員、商品類別、暫存媒體，及金幣/鑽石兩個道具
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();
        $coin = Item::factory()->create(['name' => '金幣']);
        $diamond = Item::factory()->create(['name' => '鑽石']);

        // WHEN  送出建立請求，productRewards 帶入金幣與鑽石兩筆不同 itemId
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'productRewards' => [
                    ['itemId' => $coin->id, 'quantity' => 100],
                    ['itemId' => $diamond->id, 'quantity' => 5],
                ],
            ]));

        // THEN  回傳 200，商品已建立，圖片已轉移，兩筆獎勵明細皆正確寫入
        $response->assertOk()->assertJsonPath('data', []);
        $product = Product::where('name', '新手禮包')->first();
        $this->assertNotNull($product);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'model_id' => $product->id,
            'collection_name' => CollectionName::PRODUCT->value,
        ]);
        $this->assertDatabaseHas('product_rewards', ['product_id' => $product->id, 'item_id' => $coin->id, 'quantity' => 100]);
        $this->assertDatabaseHas('product_rewards', ['product_id' => $product->id, 'item_id' => $diamond->id, 'quantity' => 5]);
    }

    /** amount 為 0 時回傳 422（現行規則要求至少為 1） */
    public function testRejectsZeroAmount(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();
        $item = Item::factory()->create();

        // WHEN  amount 為 0
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'amount' => 0,
                'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
            ]));

        // THEN  回傳 422，errors.amount 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    /** amount 為負數時回傳 422 */
    public function testRejectsNegativeAmount(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();
        $item = Item::factory()->create();

        // WHEN  amount 為負數
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'amount' => -1,
                'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
            ]));

        // THEN  回傳 422，errors.amount 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    /** 未帶 productRewards 時回傳 422（現行規則要求必填且至少一筆） */
    public function testRejectsMissingProductRewards(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();

        // WHEN  body 不含 productRewards
        $payload = $this->payload(['productTypeId' => $productType->id, 'mediaId' => $media->id]);
        unset($payload['productRewards']);
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $payload);

        // THEN  回傳 422，errors.productRewards 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards']);
    }

    /** productRewards 為空陣列時回傳 422 */
    public function testRejectsEmptyProductRewards(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();

        // WHEN  productRewards 為空陣列
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'productRewards' => [],
            ]));

        // THEN  回傳 422，errors.productRewards 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards']);
    }

    /** name 超過 50 字元時回傳 422 */
    public function testRejectsNameExceeding50Chars(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();
        $item = Item::factory()->create();

        // WHEN  name 為 51 字元
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'name' => str_repeat('a', 51),
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
            ]));

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** productTypeId 不存在於 product_types 時回傳 422 */
    public function testRejectsNonexistentProductTypeId(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $media = $this->temporaryMedia();
        $item = Item::factory()->create();

        // WHEN  productTypeId 為不存在的 id
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => 999999,
                'mediaId' => $media->id,
                'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
            ]));

        // THEN  回傳 422，errors.productTypeId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productTypeId']);
    }

    /** status 為非法值時回傳 422 */
    public function testRejectsInvalidStatus(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();
        $item = Item::factory()->create();

        // WHEN  status 為不合法的值
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'status' => 'unknown',
                'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
            ]));

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** productRewards 任一筆缺少 itemId 時回傳 422 */
    public function testRejectsRewardMissingItemId(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();

        // WHEN  productRewards 該筆缺少 itemId
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'productRewards' => [['quantity' => 1]],
            ]));

        // THEN  回傳 422，errors 包含 productRewards.0.itemId 的驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards.0.itemId']);
    }

    /** productRewards 任一筆的 itemId 指向不存在於 items 表的 id 時回傳 422 */
    public function testRejectsRewardWithNonexistentItemId(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();

        // WHEN  productRewards 該筆 itemId 為不存在的道具 id
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'productRewards' => [['itemId' => 999999, 'quantity' => 1]],
            ]));

        // THEN  回傳 422，errors 包含 productRewards.0.itemId 的驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards.0.itemId']);
    }

    /** productRewards 中出現重複 itemId 時回傳 422 */
    public function testRejectsDuplicateItemIdInRewards(): void
    {
        // GIVEN 有權限的管理員與必要前置資料，及同一個道具
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();
        $item = Item::factory()->create();

        // WHEN  productRewards 帶入兩筆相同的 itemId
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'productRewards' => [
                    ['itemId' => $item->id, 'quantity' => 1],
                    ['itemId' => $item->id, 'quantity' => 2],
                ],
            ]));

        // THEN  回傳 422，errors 包含 productRewards.*.itemId 的驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['productRewards.0.itemId', 'productRewards.1.itemId']);
    }

    /** mediaId 未提供時回傳 422（現行規則要求必填） */
    public function testRejectsMissingMediaId(): void
    {
        // GIVEN 有權限的管理員與必要前置資料
        $actor = $this->adminWith('create_products');
        $productType = ProductType::factory()->create();
        $item = Item::factory()->create();

        // WHEN  body 不含 mediaId
        $payload = $this->payload([
            'productTypeId' => $productType->id,
            'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
        ]);
        unset($payload['mediaId']);
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $payload);

        // THEN  回傳 422，errors.mediaId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mediaId']);
    }

    /** 缺少 create_products 權限時回傳 403（帶完整合法內容，確保測到的是權限檢查而非驗證錯誤） */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及完整合法的建立請求前置資料
        $actor = $this->adminWithNoPermission();
        $productType = ProductType::factory()->create();
        $media = $this->temporaryMedia();
        $item = Item::factory()->create();

        // WHEN  發送建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/products', $this->payload([
                'productTypeId' => $productType->id,
                'mediaId' => $media->id,
                'productRewards' => [['itemId' => $item->id, 'quantity' => 1]],
            ]));

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => '新手禮包',
            'amount' => 300,
            'status' => Status::UNPUBLISHED->value,
        ], $overrides);
    }

    private function temporaryMedia(): Media
    {
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();

        return $owner->addMedia(UploadedFile::fake()->image('product.png'))
            ->toMediaCollection(CollectionName::TEMPORARY->value);
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
