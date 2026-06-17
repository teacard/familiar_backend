<?php

namespace App\Http\Requests\AdminApi\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email'    => trans('admin.attributes.adminapi.email'),
            'password' => trans('admin.attributes.adminapi.password'),
        ];
    }
}
