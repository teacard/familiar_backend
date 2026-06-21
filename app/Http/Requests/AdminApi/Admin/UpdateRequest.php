<?php

namespace App\Http\Requests\AdminApi\Admin;

use App\Enums\Admin\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:15', Rule::unique('admins', 'name')->ignore($id)],
            'email' => ['required', 'email', Rule::unique('admins', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['nullable', 'string'],
            'roleId' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['required', Rule::enum(Status::class)],
            'mediaId' => ['nullable', 'integer', 'exists:media,id'],
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
            'mediaId' => trans('admin.attributes.adminapi.mediaId'),
        ];
    }
}
