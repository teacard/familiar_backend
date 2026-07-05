<?php

namespace App\Services;

use App\Data\AdminApi\Request\Item\IndexRequestData;
use App\Data\AdminApi\Request\Item\SelectRequestData;
use App\Data\AdminApi\Request\Item\StoreRequestData;
use App\Data\AdminApi\Request\Item\UpdateRequestData;
use App\Enums\ApiCode;
use App\Enums\Item\Status;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\Item;
use App\Repositories\Applications\Item\ItemRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ItemService
{
    use AsRepositoryProxy;

    public function __construct(
        protected ItemRepository $repository,
    ) {
    }

    /** 列表（關鍵字 + 啟用狀態篩選 + 分頁，含圖片與 productRewards exists 判斷供 isDeletable 使用） */
    public function listItems(IndexRequestData $data): LengthAwarePaginator
    {
        $paginator = $this->paginate(
            perPage: $data->perPage,
            page: $data->page,
            filters: [
                'keyword' => $data->keyword,
                'status' => $data->status?->value,
                'orderByDesc' => ['created_at', 'id'],
            ],
        );

        $paginator->getCollection()->load('media')->loadExists('productRewards');

        return $paginator;
    }

    /** 取得道具（不分頁），供前端下拉選單使用；isActive=true 時僅回傳啟用中道具（供建立/編輯商品獎勵時排除停用道具，比照 RoleService::selectRoles() 排除 is_system 角色），未帶時回傳所有狀態（供商品依道具篩選時，仍可搜尋到已停用道具的既有商品） */
    public function selectItems(SelectRequestData $data): Collection
    {
        return $this->get([
            'status' => $data->isActive ? Status::ACTIVE->value : null,
        ]);
    }

    /** 依 id 查詢，找不到拋 404 */
    public function findOrFail(int $id): Item
    {
        /** @var Item|null $item */
        $item = $this->findById($id);

        throw_unless(
            condition: $item,
            exception: NotFoundException::class,
        );

        return $item;
    }

    /** 建立道具（圖片轉移由 Controller 呼叫 MediaService 處理） */
    public function createItem(StoreRequestData $data): Item
    {
        return $this->create([
            'name' => $data->name,
            'status' => $data->status->value,
        ]);
    }

    /** 更新道具名稱與啟用狀態（圖片轉移由 Controller 呼叫 MediaService 處理） */
    public function updateItem(Item $item, UpdateRequestData $data): void
    {
        $item->update([
            'name' => $data->name,
            'status' => $data->status->value,
        ]);
    }

    /** 刪除道具（仍被商品獎勵明細使用時拋 422，不可刪除；若競速通過上方檢查後仍被外鍵擋下，攔截後同樣轉為 422；呼叫端須包在交易內） */
    public function deleteItem(Item $item): void
    {
        $item->newQuery()->whereKey($item->id)->lockForUpdate()->firstOrFail();

        throw_if(
            condition: $item->productRewards()->exists(),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::ITEM_IN_USE->value),
                ApiCode::ITEM_IN_USE,
            ),
        );

        try {
            $item->delete();
        } catch (QueryException $e) {
            // SQLSTATE 23000 以外的錯誤不是本例外處理的對象，原樣往外拋
            if ('23000' !== $e->getCode()) {
                throw $e;
            }

            // 23000 = 外鍵完整性違反，代表競速通過了上面的 exists() 檢查，仍視為道具使用中
            throw new UnprocessableException(trans('api-codes.' . ApiCode::ITEM_IN_USE->value), ApiCode::ITEM_IN_USE);
        }
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
