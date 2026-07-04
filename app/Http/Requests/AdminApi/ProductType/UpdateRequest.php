<?php

namespace App\Http\Requests\AdminApi\ProductType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:10'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('product.attributes.adminapi.name'),
        ];
    }
}
