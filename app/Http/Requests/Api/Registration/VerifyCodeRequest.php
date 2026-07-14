<?php

namespace App\Http\Requests\Api\Registration;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:6'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => trans('registration.attributes.api.code'),
        ];
    }
}
