<?php

namespace App\Http\Requests\AdminApi\Admin;

use App\Enums\Admin\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::enum(Status::class)],
            'roleId' => ['nullable', 'integer'],
        ] + config('pagination.request');
    }

    public function attributes(): array
    {
        return [
            'keyword' => trans('admin.attributes.adminapi.keyword'),
            'status' => trans('admin.attributes.adminapi.status'),
            'roleId' => trans('admin.attributes.adminapi.roleId'),
        ] + trans('pagination.attributes');
    }
}
