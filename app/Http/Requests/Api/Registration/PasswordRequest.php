<?php

namespace App\Http\Requests\Api\Registration;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'password' => trans('registration.attributes.api.password'),
            'passwordConfirmation' => trans('registration.attributes.api.passwordConfirmation'),
        ];
    }
}
