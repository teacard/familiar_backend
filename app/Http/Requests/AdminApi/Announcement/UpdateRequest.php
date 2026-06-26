<?php

namespace App\Http\Requests\AdminApi\Announcement;

use App\Enums\Announcement\TargetAudience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:5000'],
            'targetAudience' => ['required', Rule::enum(TargetAudience::class)],
            'publishAt' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'expiresAt' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:publishAt'],
            'shouldPin' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => trans('announcement.attributes.adminapi.title'),
            'content' => trans('announcement.attributes.adminapi.content'),
            'targetAudience' => trans('announcement.attributes.adminapi.targetAudience'),
            'publishAt' => trans('announcement.attributes.adminapi.publishAt'),
            'expiresAt' => trans('announcement.attributes.adminapi.expiresAt'),
            'shouldPin' => trans('announcement.attributes.adminapi.shouldPin'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'shouldPin' => filter_var($this->shouldPin ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
