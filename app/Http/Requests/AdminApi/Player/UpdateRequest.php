<?php

namespace App\Http\Requests\AdminApi\Player;

use App\Enums\Player\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', Rule::unique('players', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('players', 'phone')->ignore($id)],
            'status' => ['required', Rule::enum(Status::class)],
            'password' => ['nullable', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['nullable', 'string'],
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
