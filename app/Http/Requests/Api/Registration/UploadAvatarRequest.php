<?php

namespace App\Http\Requests\Api\Registration;

use App\Enums\Media\UploadFileType;
use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'file', 'mimes:' . UploadFileType::IMAGE->mimes(), 'max:' . UploadFileType::IMAGE->maxes()],
        ];
    }

    public function attributes(): array
    {
        return [
            'avatar' => trans('registration.attributes.api.avatar'),
        ];
    }
}
