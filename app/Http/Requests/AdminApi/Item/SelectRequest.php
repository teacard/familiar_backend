<?php

namespace App\Http\Requests\AdminApi\Item;

use Illuminate\Foundation\Http\FormRequest;

class SelectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'isActive' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'isActive' => trans('item.attributes.adminapi.isActive'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('isActive')) {
            $this->merge([
                'isActive' => filter_var($this->isActive, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
