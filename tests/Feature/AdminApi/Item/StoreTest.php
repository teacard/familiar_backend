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

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** 合法資料可成功建立道具，並將暫存媒體轉移至道具的圖片集合 */
    public function testCreatesItemWithImage(): void
    {
        // GIVEN 有 create_items 權限的管理員，及一筆暫存媒體
        $actor = $this->adminWith('create_items');
        $media = $this->temporaryMedia();

        // WHEN  送出建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $this->payload(['mediaId' => $media->id]));

        // THEN  回傳 200，道具已建立，圖片已轉移
        $response->assertOk()->assertJsonPath('data', []);
        $item = Item::where('name', '金幣')->first();
        $this->assertNotNull($item);
        $this->assertSame(Status::ACTIVE, $item->status);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'model_id' => $item->id,
            'collection_name' => CollectionName::ITEM->value,
        ]);
    }

    /** name 超過 10 字元時回傳 422 */
    public function testRejectsNameExceeding10Chars(): void
    {
        // GIVEN 有權限的管理員，及一筆暫存媒體
        $actor = $this->adminWith('create_items');
        $media = $this->temporaryMedia();

        // WHEN  name 為 11 字元
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $this->payload(['name' => str_repeat('道', 11), 'mediaId' => $media->id]));

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** 未提供 name 時回傳 422 */
    public function testRejectsMissingName(): void
    {
        // GIVEN 有權限的管理員，及一筆暫存媒體
        $actor = $this->adminWith('create_items');
        $media = $this->temporaryMedia();

        // WHEN  body 不含 name
        $payload = $this->payload(['mediaId' => $media->id]);
        unset($payload['name']);
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $payload);

        // THEN  回傳 422，errors.name 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /** 未提供 status 時回傳 422 */
    public function testRejectsMissingStatus(): void
    {
        // GIVEN 有權限的管理員，及一筆暫存媒體
        $actor = $this->adminWith('create_items');
        $media = $this->temporaryMedia();

        // WHEN  body 不含 status
        $payload = $this->payload(['mediaId' => $media->id]);
        unset($payload['status']);
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $payload);

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** status 為非法值時回傳 422 */
    public function testRejectsInvalidStatus(): void
    {
        // GIVEN 有權限的管理員，及一筆暫存媒體
        $actor = $this->adminWith('create_items');
        $media = $this->temporaryMedia();

        // WHEN  status 為不合法的值
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $this->payload(['mediaId' => $media->id, 'status' => 'unknown']));

        // THEN  回傳 422，errors.status 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /** 未提供 mediaId 時回傳 422 */
    public function testRejectsMissingMediaId(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_items');

        // WHEN  body 不含 mediaId
        $payload = $this->payload();
        unset($payload['mediaId']);
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $payload);

        // THEN  回傳 422，errors.mediaId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mediaId']);
    }

    /** mediaId 指向不存在的媒體時回傳 422 */
    public function testRejectsNonexistentMediaId(): void
    {
        // GIVEN 有權限的管理員
        $actor = $this->adminWith('create_items');

        // WHEN  mediaId 為不存在的媒體 id
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $this->payload(['mediaId' => 999999]));

        // THEN  回傳 422，errors.mediaId 包含驗證失敗訊息
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mediaId']);
    }

    /** 缺少 create_items 權限時回傳 403 */
    public function testReturns403WhenMissingPermission(): void
    {
        // GIVEN 無權限的管理員，及一筆暫存媒體
        $actor = $this->adminWithNoPermission();
        $media = $this->temporaryMedia();

        // WHEN  發送建立請求
        $response = $this->actingAs($actor, 'admin')
            ->postJson('/admin-api/items', $this->payload(['mediaId' => $media->id]));

        // THEN  回傳 403
        $response->assertForbidden();
    }

    /** 未認證請求回傳 401 */
    public function testReturns401WhenUnauthenticated(): void
    {
        // GIVEN 未攜帶 Token
        // WHEN  發送 POST /admin-api/items
        $response = $this->postJson('/admin-api/items', $this->payload());

        // THEN  回傳 401
        $response->assertUnauthorized();
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => '金幣',
            'status' => Status::ACTIVE->value,
        ], $overrides);
    }

    private function temporaryMedia(): Media
    {
        Storage::fake('minio', ['url' => 'http://localhost/media']);
        $this->seed(TemporaryMediaSeeder::class);
        $owner = TemporaryMedia::where('system_name', SystemName::ADMIN->value)->first();

        return $owner->addMedia(UploadedFile::fake()->image('item.png'))
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
