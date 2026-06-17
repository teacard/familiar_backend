<?php

namespace App\Http\Requests\AdminApi\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:15', 'unique:admins,name'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
            'roleId' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('admin.attributes.adminapi.name'),
            'email' => trans('admin.attributes.adminapi.email'),
            'password' => trans('admin.attributes.adminapi.password'),
            'roleId' => trans('admin.attributes.adminapi.roleId'),
        ];
    }
}
