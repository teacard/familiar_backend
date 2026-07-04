<?php

namespace App\Http\Requests\AdminApi\Product;

use App\Enums\Media\CollectionName;
use App\Enums\Product\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
                Rule::exists('media', 'id')->where('collection_name', CollectionName::TEMPORARY->value),
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
