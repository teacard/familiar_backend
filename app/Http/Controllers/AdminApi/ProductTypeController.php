<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\ProductType\IndexRequestData;
use App\Data\AdminApi\Request\ProductType\StoreRequestData;
use App\Data\AdminApi\Request\ProductType\UpdateRequestData;
use App\Data\AdminApi\Response\ProductType\ProductTypePaginatedResponse;
use App\Data\AdminApi\Response\ProductType\ProductTypeSelectResponse;
use App\Enums\Auth\Guard;
use App\Enums\Permission\Name;
use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\ProductType\IndexRequest;
use App\Http\Requests\AdminApi\ProductType\StoreRequest;
use App\Http\Requests\AdminApi\ProductType\UpdateRequest;
use App\Models\Admin;
use App\Services\ProductTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductTypeController extends Controller
{
    public function __construct(
        protected ProductTypeService $productTypeService,
    ) {
    }

    /** 商品類別管理-列表 */
    public function index(IndexRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_PRODUCT_TYPES->value),
            exception: ForbiddenException::class,
        );

        $paginator = $this->productTypeService->listProductTypes(
            IndexRequestData::fromRequest($request)
        );

        return $this->success(
            ProductTypePaginatedResponse::fromPaginator($paginator)
        );
    }

    /** 商品類別管理-下拉選單 */
    public function select(): JsonResponse
    {
        // 僅需登入即可取得，不額外檢查權限（供新增/編輯商品表單使用）
        return $this->success(
            ProductTypeSelectResponse::collect(
                $this->productTypeService->selectProductTypes()
            )
        );
    }

    /** 商品類別管理-新增 */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::CREATE_PRODUCT_TYPES->value),
            exception: ForbiddenException::class,
        );

        $this->productTypeService->createProductType(
            StoreRequestData::fromRequest($request)
        );

        return $this->success([]);
    }

    /** 商品類別管理-編輯 */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::EDIT_PRODUCT_TYPES->value),
            exception: ForbiddenException::class,
        );

        $this->productTypeService->updateProductType(
            productType: $this->productTypeService->findOrFail($id),
            data: UpdateRequestData::fromRequest($request),
        );

        return $this->success([]);
    }

    /** 商品類別管理-刪除 */
    public function destroy(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::DELETE_PRODUCT_TYPES->value),
            exception: ForbiddenException::class,
        );

        $productType = $this->productTypeService->findOrFail($id);

        DB::transaction(function () use ($productType): void {
            $this->productTypeService->deleteProductType($productType);
        });

        return $this->success([]);
    }
}
