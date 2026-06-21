<?php

namespace App\Http\Requests\AdminApi\Media;

use App\Enums\Media\UploadFileType;
use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:' . UploadFileType::IMAGE->mimes(), 'max:' . UploadFileType::IMAGE->maxes()],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => trans('media.attributes.adminapi.file'),
        ];
    }
}
