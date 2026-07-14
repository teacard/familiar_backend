<?php

namespace App\Http\Requests\Api\Registration;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'mediaId' => ['required', 'integer', 'exists:media,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('registration.attributes.api.name'),
            'mediaId' => trans('registration.attributes.api.mediaId'),
        ];
    }
}
