<?php

namespace App\Http\Requests\AdminApi\Admin;

use App\Enums\Admin\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'status' => ['required', Rule::enum(Status::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('admin.attributes.adminapi.name'),
            'email' => trans('admin.attributes.adminapi.email'),
            'password' => trans('admin.attributes.adminapi.password'),
            'roleId' => trans('admin.attributes.adminapi.roleId'),
            'status' => trans('admin.attributes.adminapi.status'),
        ];
    }
}
