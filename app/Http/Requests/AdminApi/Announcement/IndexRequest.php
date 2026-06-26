<?php

namespace App\Http\Requests\AdminApi\Announcement;

use App\Enums\Announcement\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(Status::class)],
            'publishAtStart' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'publishAtEnd' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'pinned' => ['nullable', 'boolean'],
        ] + config('pagination.request');
    }

    public function attributes(): array
    {
        return [
            'keyword' => trans('announcement.attributes.adminapi.keyword'),
            'status' => trans('announcement.attributes.adminapi.status'),
            'publishAtStart' => trans('announcement.attributes.adminapi.publishAtStart'),
            'publishAtEnd' => trans('announcement.attributes.adminapi.publishAtEnd'),
            'pinned' => trans('announcement.attributes.adminapi.pinned'),
        ] + trans('pagination.attributes');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('pinned')) {
            $this->merge([
                'pinned' => filter_var($this->pinned, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
