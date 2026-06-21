<?php

namespace App\Repositories\Applications\Media;

use App\Enums\Media\CollectionName;
use App\Repositories\Repository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRepository extends Repository
{
    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    /** 將暫存集合的指定 media 改掛至目標 model 與集合（僅更新 DB，不搬移實體檔案） */
    public function updateTemporaryToModel(
        int $mediaId,
        string $modelType,
        int $modelId,
        string $collectionName,
    ): int {
        return $this->update(
            filters: [
                'id' => $mediaId,
                'collection_name' => CollectionName::TEMPORARY->value,
            ],
            attributes: [
                'model_type' => $modelType,
                'model_id' => $modelId,
                'collection_name' => $collectionName,
            ],
        );
    }
}
