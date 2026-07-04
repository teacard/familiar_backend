<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Request\Product\IndexRequestData;
use App\Data\AdminApi\Request\Product\StoreRequestData;
use App\Data\AdminApi\Request\Product\UpdateRequestData;
use App\Data\AdminApi\Response\Product\ProductPaginatedResponse;
use App\Data\AdminApi\Response\Product\ProductResponse;
use App\Enums\Auth\Guard;
use App\Enums\Media\CollectionName;
use App\Enums\Permission\Name;
use App\Exceptions\ForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Product\IndexRequest;
use App\Http\Requests\AdminApi\Product\StoreRequest;
use App\Http\Requests\AdminApi\Product\UpdateRequest;
use App\Models\Admin;
use App\Services\MediaService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected MediaService $mediaService,
    ) {
    }

    /** 商品管理-列表 */
    public function index(IndexRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_PRODUCTS->value),
            exception: ForbiddenException::class,
        );

        $paginator = $this->productService->listProducts(
            IndexRequestData::fromRequest($request)
        );

        return $this->success(
            ProductPaginatedResponse::fromPaginator($paginator)
        );
    }

    /** 商品管理-新增 */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::CREATE_PRODUCTS->value),
            exception: ForbiddenException::class,
        );

        $data = StoreRequestData::fromRequest($request);

        DB::transaction(function () use ($data): void {
            $product = $this->productService->createProduct($data);

            $this->mediaService->transferToModel($data->mediaId, $product, CollectionName::PRODUCT);
        });

        return $this->success([]);
    }

    /** 商品管理-詳情 */
    public function show(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::VIEW_PRODUCTS->value),
            exception: ForbiddenException::class,
        );

        $product = $this->productService->findOrFail($id);
        $product->load('media', 'productType', 'productRewards.item');

        return $this->success(
            ProductResponse::fromModel($product)
        );
    }

    /** 商品管理-編輯 */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::EDIT_PRODUCTS->value),
            exception: ForbiddenException::class,
        );

        $product = $this->productService->findOrFail($id);
        $data = UpdateRequestData::fromRequest($request);

        DB::transaction(function () use ($product, $data): void {
            $this->productService->updateProduct(product: $product, data: $data);

            $this->mediaService->transferToModel($data->mediaId, $product, CollectionName::PRODUCT);
        });

        return $this->success([]);
    }

    /** 商品管理-刪除 */
    public function destroy(int $id): JsonResponse
    {
        /** @var Admin $actor */
        $actor = auth(Guard::ADMIN->value)->user();

        throw_unless(
            condition: $actor->getAllPermissions()->contains('name', Name::DELETE_PRODUCTS->value),
            exception: ForbiddenException::class,
        );

        $product = $this->productService->findOrFail($id);

        DB::transaction(function () use ($product): void {
            $this->productService->deleteProduct($product);
        });

        return $this->success([]);
    }
}
