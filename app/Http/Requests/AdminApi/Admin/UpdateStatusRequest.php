<?php

namespace App\Http\Requests\AdminApi\Admin;

use App\Enums\Admin\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(Status::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => trans('admin.attributes.adminapi.status'),
        ];
    }
}
