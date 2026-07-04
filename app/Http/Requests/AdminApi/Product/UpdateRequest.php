<?php

namespace App\Http\Requests\AdminApi\Product;

use App\Enums\Media\CollectionName;
use App\Enums\Product\Status;
use App\Models\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'productTypeId' => ['required', 'integer', 'exists:product_types,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(Status::class)],
            'mediaId' => [
                'required',
                'integer',
                // 允許新上傳的暫存媒體，或本商品目前已掛載的主圖（未換圖時前端會原樣送回）
                Rule::exists('media', 'id')->where(function (Builder $query): void {
                    $query->where('collection_name', CollectionName::TEMPORARY->value)
                        ->orWhere(function (Builder $query): void {
                            $query->where('collection_name', CollectionName::PRODUCT->value)
                                ->where('model_type', (new Product())->getMorphClass())
                                ->where('model_id', $this->route('id'));
                        });
                }),
            ],
            'productRewards' => ['required', 'array', 'min:1'],
            'productRewards.*.itemId' => ['required', 'integer', 'exists:items,id', 'distinct'],
            'productRewards.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('product.attributes.adminapi.name'),
            'productTypeId' => trans('product.attributes.adminapi.productTypeId'),
            'amount' => trans('product.attributes.adminapi.amount'),
            'status' => trans('product.attributes.adminapi.status'),
            'mediaId' => trans('product.attributes.adminapi.mediaId'),
            'productRewards' => trans('product.attributes.adminapi.productRewards'),
        ];
    }
}
