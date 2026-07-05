<?php

namespace App\Http\Requests\AdminApi\Item;

use App\Enums\Item\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', Rule::enum(Status::class)],
        ] + config('pagination.request');
    }

    public function attributes(): array
    {
        return [
            'keyword' => trans('item.attributes.adminapi.keyword'),
            'status' => trans('item.attributes.adminapi.status'),
        ] + trans('pagination.attributes');
    }
}
