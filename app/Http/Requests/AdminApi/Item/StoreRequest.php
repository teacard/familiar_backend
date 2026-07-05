<?php

namespace App\Http\Requests\AdminApi\Item;

use App\Enums\Item\Status;
use App\Enums\Media\CollectionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:10'],
            'status' => ['required', Rule::enum(Status::class)],
            'mediaId' => [
                'required',
                'integer',
                Rule::exists('media', 'id')->where('collection_name', CollectionName::TEMPORARY->value),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('item.attributes.adminapi.name'),
            'status' => trans('item.attributes.adminapi.status'),
            'mediaId' => trans('item.attributes.adminapi.mediaId'),
        ];
    }
}
