<?php

namespace App\Http\Requests\AdminApi\Role;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:50'],
        ] + config('pagination.request');
    }

    public function attributes(): array
    {
        return [
            'keyword' => trans('admin.attributes.adminapi.keyword'),
        ] + trans('pagination.attributes');
    }
}
