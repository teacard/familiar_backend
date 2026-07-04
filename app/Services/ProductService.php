<?php

namespace App\Services;

use App\Data\AdminApi\Request\Product\IndexRequestData;
use App\Data\AdminApi\Request\Product\ProductRewardData;
use App\Data\AdminApi\Request\Product\StoreRequestData;
use App\Data\AdminApi\Request\Product\UpdateRequestData;
use App\Exceptions\NotFoundException;
use App\Models\Product;
use App\Repositories\Applications\Product\ProductRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService
{
    use AsRepositoryProxy;

    public function __construct(
        protected ProductRepository $repository,
    ) {
    }

    /** 列表（關鍵字 + 種類 + 狀態篩選 + 分頁，含商品類別 eager load） */
    public function listProducts(IndexRequestData $data): LengthAwarePaginator
    {
        $paginator = $this->paginate(
            perPage: $data->perPage,
            page: $data->page,
            filters: [
                'keyword' => $data->keyword,
                'productTypeId' => $data->productTypeId,
                'status' => $data->status?->value,
                'orderByDesc' => ['created_at', 'id'],
            ],
        );

        $paginator->getCollection()->load('productType');

        return $paginator;
    }

    /** 單筆查詢（找不到拋 404；不預先 eager load，關聯由呼叫端依需求自行 load） */
    public function findOrFail(int $id): Product
    {
        /** @var Product|null $product */
        $product = $this->findById($id);

        throw_unless(
            condition: $product,
            exception: NotFoundException::class,
        );

        return $product;
    }

    /** 建立商品，並一併建立獎勵明細（至少一筆） */
    public function createProduct(StoreRequestData $data): Product
    {
        /** @var Product $product */
        $product = $this->create([
            'name' => $data->name,
            'product_type_id' => $data->productTypeId,
            'amount' => $data->amount,
            'status' => $data->status->value,
        ]);

        $this->syncProductRewards($product, $data->productRewards);

        return $product;
    }

    /** 更新商品欄位；productRewards 採整批覆蓋（先刪除既有明細再依請求內容重建） */
    public function updateProduct(Product $product, UpdateRequestData $data): void
    {
        $product->update([
            'name' => $data->name,
            'product_type_id' => $data->productTypeId,
            'amount' => $data->amount,
            'status' => $data->status->value,
        ]);

        $product->productRewards()->delete();
        $this->syncProductRewards($product, $data->productRewards);
    }

    /** 刪除商品（硬刪除；product_rewards 由資料庫層 cascadeOnDelete 一併清除） */
    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    /** 依 productRewards 建立對應的 product_rewards 明細（單次批次 insert，避免逐筆呼叫 SQL） */
    protected function syncProductRewards(Product $product, Collection $productRewards): void
    {
        $now = now();

        $product->productRewards()->insert(
            $productRewards->map(static fn (ProductRewardData $reward): array => [
                'product_id' => $product->id,
                'item_id' => $reward->itemId,
                'quantity' => $reward->quantity,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
