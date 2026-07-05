<?php

namespace Tests\Feature\AdminApi\Item;

use App\Enums\Item\Status;
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

class ListTest extends TestCase
{
    use RefreshDatabase;

    /** 成功取得道具分頁列表，items 每筆含 id、name、status、image、isDeletable */
    public function testReturnsItemListWithImageAndIsDeletable(): void
    {
        // GIVEN 有 view_items 權限的管理員，及一筆含圖片的道具
        $actor = $this->adminWith('view_items');
        $this->itemWithImage(['name' => '金幣']);

        // WHEN  發送 GET /admin-api/items
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items');

        // THEN  回傳 200，items 每筆含 id、name、status、image、isDeletable
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'name', 'status', 'image' => ['id', 'url'], 'isDeletable'],
                    ],
                    'currentPage',
                    'perPage',
                    'lastPage',
                ],
            ]);

        foreach ($response->json('data.items') as $entry) {
            $this->assertIsBool($entry['isDeletable']);
        }
    }

    /** status 篩選，只回傳符合狀態的道具 */
    public function testFiltersByStatus(): void
    {
        // GIVEN 有權限的管理員，及一筆啟用、一筆停用的道具
        $actor = $this->adminWith('view_items');
        $this->itemWithImage(['name' => '啟用中', 'status' => Status::ACTIVE]);
        $this->itemWithImage(['name' => '已停用', 'status' => Status::DISABLED]);

        // WHEN  發送 GET /admin-api/items?status=disabled
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items?status=' . Status::DISABLED->value);

        // THEN  只回傳狀態為停用的道具
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('已停用', $names);
        $this->assertNotContains('啟用中', $names);
    }

    /** status 傳入不合法值時回傳 422 */
    public function testRejectsInvalidStatus(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('view_items');

        // WHEN  傳入不合法的 status
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items?status=unknown');

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** 道具仍被商品獎勵明細使用時，isDeletable 為 false */
    public function testIsDeletableFalseWhenItemInUse(): void
    {
        // GIVEN 有權限的管理員，及一個被商品獎勵明細使用的道具
        $actor = $this->adminWith('view_items');
        $item = $this->itemWithImage(['name' => '使用中']);
        $product = Product::factory()->create();
        $product->productRewards()->create(['item_id' => $item->id, 'quantity' => 1]);

        // WHEN  發送 GET /admin-api/items
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items');

        // THEN  該道具的 isDeletable 為 false
        $response->assertOk();
        $entry = collect($response->json('data.items'))->firstWhere('name', '使用中');

        $this->assertNotNull($entry);
        $this->assertFalse($entry['isDeletable']);
    }

    /** 道具沒有任何商品獎勵明細使用時，isDeletable 為 true */
    public function testIsDeletableTrueWhenItemUnused(): void
    {
        // GIVEN 有權限的管理員，及一個無人使用的道具
        $actor = $this->adminWith('view_items');
        $this->itemWithImage(['name' => '未使用']);

        // WHEN  發送 GET /admin-api/items
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items');

        // THEN  該道具的 isDeletable 為 true
        $response->assertOk();
        $entry = collect($response->json('data.items'))->firstWhere('name', '未使用');

        $this->assertNotNull($entry);
        $this->assertTrue($entry['isDeletable']);
    }

    /** keyword 模糊搜尋，只回傳 name 含關鍵字的道具 */
    public function testFiltersByKeyword(): void
    {
        // GIVEN 有權限的管理員，及兩個 name 不同的道具
        $actor = $this->adminWith('view_items');
        $this->itemWithImage(['name' => '金幣']);
        $this->itemWithImage(['name' => '鑽石']);

        // WHEN  發送 GET /admin-api/items?keyword=金
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items?keyword=金');

        // THEN  只回傳 name 含「金」的道具
        $response->assertOk();
        $names = collect($response->json('data.items'))->pluck('name');
        $this->assertContains('金幣', $names);
        $this->assertNotContains('鑽石', $names);
    }

    /** perPage=25 時每頁最多 25 筆，且 perPage 回傳 25 */
    public function testRespectsPerPage(): void
    {
        // GIVEN 有權限的管理員，及 30 個道具
        $actor = $this->adminWith('view_items');
        for ($i = 0; $i < 30; ++$i) {
            $this->itemWithImage();
        }

        // WHEN  帶 perPage=25
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items?perPage=25');

        // THEN  perPage 為 25，且 items 最多 25 筆
        $response->assertOk()
            ->assertJsonPath('data.perPage', 25);
        $this->assertCount(25, $response->json('data.items'));
    }

    /** perPage 傳入不合法值時回傳 422，errors.perPage 含錯誤訊息 */
    public function testRejectsInvalidPerPage(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('view_items');

        // WHEN  傳入 perPage=100
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items?perPage=100');

        // THEN  回傳 422，errors.perPage 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['perPage']);
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 GET /admin-api/items
        $response = $this->getJson('/admin-api/items');

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** 缺少 view_items 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 有 role 但無任何 permission 的管理員
        $actor = $this->adminWithNoPermission();

        // WHEN  發送 GET /admin-api/items
        $response = $this->actingAs($actor, 'admin')
            ->getJson('/admin-api/items');

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
