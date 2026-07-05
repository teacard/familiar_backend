<?php

namespace App\Http\Requests\AdminApi\Item;

use App\Enums\Item\Status;
use App\Enums\Media\CollectionName;
use App\Models\Item;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:10'],
            'status' => ['required', Rule::enum(Status::class)],
            'mediaId' => [
                'required',
                'integer',
                // 允許新上傳的暫存媒體，或本道具目前已掛載的圖片（未換圖時前端會原樣送回）
                Rule::exists('media', 'id')->where(function (Builder $query): void {
                    $query->where('collection_name', CollectionName::TEMPORARY->value)
                        ->orWhere(function (Builder $query): void {
                            $query->where('collection_name', CollectionName::ITEM->value)
                                ->where('model_type', (new Item())->getMorphClass())
                                ->where('model_id', $this->route('id'));
                        });
                }),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('item.attributes.adminapi.name'),
            'status' => trans('item.attributes.adminapi.status'),
            'mediaId' => trans('item.attributes.adminapi.mediaId'),
        ];
    }
}
