<?php

namespace App\Http\Requests\AdminApi\Player;

use App\Enums\Player\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', Rule::enum(Status::class)],
        ] + config('pagination.request');
    }

    public function attributes(): array
    {
        return [
            'keyword' => trans('player.attributes.adminapi.keyword'),
            'status' => trans('player.attributes.adminapi.status'),
        ] + trans('pagination.attributes');
    }
}
