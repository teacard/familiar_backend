<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Item\IndexRequestData;
use App\Data\AdminApi\Request\Item\SelectRequestData;
use App\Data\AdminApi\Request\Item\StoreRequestData;
use App\Data\AdminApi\Request\Item\UpdateRequestData;
use App\Data\AdminApi\Response\Item\ItemPaginatedResponse;
use App\Data\AdminApi\Response\Item\ItemSelectResponse;
use App\Data\AdminApi\Response\Item\ItemShowResponse;
use App\Enums\Auth\Guard;
use App\Enums\Media\CollectionName;
use App\Enums\Permission\Name;
use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Item\IndexRequest;
use App\Http\Requests\AdminApi\Item\SelectRequest;
use App\Http\Requests\AdminApi\Item\StoreRequest;
use App\Http\Requests\AdminApi\Item\UpdateRequest;
use App\Models\Admin;
use App\Services\ItemService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function __construct(
        protected ItemService $itemService,
        protected MediaService $mediaService,
    ) {
    }

    /** 道具管理-列表 */
    public function index(IndexRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_ITEMS->value),
            exception: ForbiddenException::class,
        );

        $paginator = $this->itemService->listItems(
            IndexRequestData::fromRequest($request)
        );

        return $this->success(
            ItemPaginatedResponse::fromPaginator($paginator)
        );
    }

    /** 道具管理-下拉選單 */
    public function select(SelectRequest $request): JsonResponse
    {
        // 僅需登入即可取得，不額外檢查權限（供新增/編輯商品表單使用）
        return $this->success(
            ItemSelectResponse::collect(
                $this->itemService->selectItems(SelectRequestData::fromRequest($request))
            )
        );
    }

    /** 道具管理-詳情 */
    public function show(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_ITEMS->value),
            exception: ForbiddenException::class,
        );

        $item = $this->itemService->findOrFail($id);
        $item->load('media');

        return $this->success(
            ItemShowResponse::fromModel($item)
        );
    }

    /** 道具管理-新增 */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::CREATE_ITEMS->value),
            exception: ForbiddenException::class,
        );

        $data = StoreRequestData::fromRequest($request);

        DB::transaction(function () use ($data): void {
            $item = $this->itemService->createItem($data);

            $this->mediaService->transferToModel($data->mediaId, $item, CollectionName::ITEM);
        });

        return $this->success([]);
    }

    /** 道具管理-編輯 */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::EDIT_ITEMS->value),
            exception: ForbiddenException::class,
        );

        $item = $this->itemService->findOrFail($id);
        $data = UpdateRequestData::fromRequest($request);

        DB::transaction(function () use ($item, $data): void {
            $this->itemService->updateItem(item: $item, data: $data);

            $this->mediaService->transferToModel($data->mediaId, $item, CollectionName::ITEM);
        });

        return $this->success([]);
    }

    /** 道具管理-刪除 */
    public function destroy(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::DELETE_ITEMS->value),
            exception: ForbiddenException::class,
        );

        $item = $this->itemService->findOrFail($id);

        DB::transaction(function () use ($item): void {
            $this->itemService->deleteItem($item);
        });

        return $this->success([]);
    }
}
