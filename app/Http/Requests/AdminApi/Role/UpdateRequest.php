<?php

namespace App\Http\Requests\AdminApi\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($id)],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('admin.attributes.adminapi.name'),
            'permissions' => trans('admin.attributes.adminapi.permissions'),
        ];
    }
}
