<?php

namespace App\Http\Requests\AdminApi\Player;

use App\Enums\Player\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'unique:players,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:players,phone'],
            'status' => ['required', Rule::enum(Status::class)],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('player.attributes.adminapi.name'),
            'email' => trans('player.attributes.adminapi.email'),
            'phone' => trans('player.attributes.adminapi.phone'),
            'status' => trans('player.attributes.adminapi.status'),
            'password' => trans('player.attributes.adminapi.password'),
            'passwordConfirmation' => trans('player.attributes.adminapi.passwordConfirmation'),
        ];
    }
}
