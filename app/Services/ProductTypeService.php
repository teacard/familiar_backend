<?php

namespace App\Services;

use App\Data\AdminApi\Request\ProductType\IndexRequestData;
use App\Data\AdminApi\Request\ProductType\StoreRequestData;
use App\Data\AdminApi\Request\ProductType\UpdateRequestData;
use App\Enums\ApiCode;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableException;
use App\Models\ProductType;
use App\Repositories\Applications\ProductType\ProductTypeRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductTypeService
{
    use AsRepositoryProxy;

    public function __construct(
        protected ProductTypeRepository $repository,
    ) {
    }

    /** 列表（關鍵字篩選 + 分頁，含 products exists 判斷供 isDeletable 使用） */
    public function listProductTypes(IndexRequestData $data): LengthAwarePaginator
    {
        $paginator = $this->paginate(
            perPage: $data->perPage,
            page: $data->page,
            filters: [
                'keyword' => $data->keyword,
                'orderByDesc' => ['created_at', 'id'],
            ],
        );

        $paginator->getCollection()->loadExists('products');

        return $paginator;
    }

    /** 取得全部類別（不分頁），供前端下拉選單使用 */
    public function selectProductTypes(): Collection
    {
        return $this->get();
    }

    /** 依 id 查詢，找不到拋 404 */
    public function findOrFail(int $id): ProductType
    {
        /** @var ProductType|null $productType */
        $productType = $this->findById($id);

        throw_unless(
            condition: $productType,
            exception: NotFoundException::class,
        );

        return $productType;
    }

    /** 建立商品類別，code 由系統自動產生（唯一 8 碼） */
    public function createProductType(StoreRequestData $data): ProductType
    {
        return $this->create([
            'name' => $data->name,
            'code' => $this->generateUniqueCode(),
        ]);
    }

    /** 更新類別名稱（code 建立後不可修改） */
    public function updateProductType(ProductType $productType, UpdateRequestData $data): void
    {
        $productType->update(['name' => $data->name]);
    }

    /** 刪除類別（仍有商品使用該類別時拋 422，不可刪除；鎖列避免刪除與商品建立競速產生孤兒外鍵；呼叫端須包在交易內） */
    public function deleteProductType(ProductType $productType): void
    {
        $productType->newQuery()->whereKey($productType->id)->lockForUpdate()->firstOrFail();

        throw_if(
            condition: $productType->products()->exists(),
            exception: new UnprocessableException(
                trans('api-codes.' . ApiCode::PRODUCT_TYPE_IN_USE->value),
                ApiCode::PRODUCT_TYPE_IN_USE,
            ),
        );

        $productType->delete();
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /** 產生唯一的 code：8 碼大寫英數，碰撞重試 */
    protected function generateUniqueCode(): string
    {
        do {
            $candidate = strtoupper(Str::random(8));
        } while ($this->exists(['code' => $candidate]));

        return $candidate;
    }
}
