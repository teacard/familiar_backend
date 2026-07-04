<?php

namespace App\Http\Requests\AdminApi\Product;

use App\Enums\Product\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:50'],
            'productTypeId' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(Status::class)],
        ] + config('pagination.request');
    }

    public function attributes(): array
    {
        return [
            'keyword' => trans('product.attributes.adminapi.keyword'),
            'productTypeId' => trans('product.attributes.adminapi.productTypeId'),
            'status' => trans('product.attributes.adminapi.status'),
        ] + trans('pagination.attributes');
    }
}
