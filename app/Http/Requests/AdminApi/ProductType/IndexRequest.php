<?php

namespace App\Http\Requests\AdminApi\ProductType;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:10'],
        ] + config('pagination.request');
    }

    public function attributes(): array
    {
        return [
            'keyword' => trans('product.attributes.adminapi.keyword'),
        ] + trans('pagination.attributes');
    }
}
