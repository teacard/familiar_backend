<?php

namespace App\Services;

use App\Enums\Media\CollectionName;
use App\Enums\Media\CustomProperty;
use App\Enums\Media\UploadFileType;
use App\Enums\TemporaryMedia\SystemName;
use App\Exceptions\NotFoundException;
use App\Models\TemporaryMedia;
use App\Repositories\Applications\Media\MediaRepository;
use App\Repositories\Applications\TemporaryMedia\TemporaryMediaRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    use AsRepositoryProxy;

    public function __construct(
        protected MediaRepository $repository,
        protected TemporaryMediaRepository $temporaryMediaRepository,
    ) {
    }

    /** 上傳檔案至暫存集合，回傳建立的媒體 */
    public function store(UploadedFile $file, UploadFileType $type, SystemName $systemName): Media
    {
        /** @var TemporaryMedia|null $owner */
        $owner = $this->temporaryMediaRepository->first(['system_name' => $systemName->value]);

        throw_unless($owner, NotFoundException::class);

        $name = "{$type->value}_" . Str::random(20);

        return $owner->addMedia($file)
            ->usingFileName("{$name}.{$file->getClientOriginalExtension()}")
            ->usingName($name)
            ->withCustomProperties([
                CustomProperty::TYPE->value => $type->value,
                CustomProperty::ORIGINAL_FILE_NAME->value => $file->getClientOriginalName(),
            ])
            ->toMediaCollection(CollectionName::TEMPORARY->value);
    }

    /** 刪除 TEMPORARY 集合中建立早於指定時間的媒體（逐筆觸發 Spatie 刪除以移除實體檔案），回傳刪除筆數 */
    public function pruneExpiredTemporary(Carbon $before): int
    {
        $count = 0;

        $this->get([
            'collection_name' => CollectionName::TEMPORARY->value,
            'created_at_lt' => $before,
        ])->each(function (Media $media) use (&$count): void {
            $media->delete();
            ++$count;
        });

        return $count;
    }

    /** 將指定暫存媒體改掛至目標 model 的指定媒體集合（僅更新 DB；找不到時回 false）；若該集合為單檔覆蓋，先清除既有媒體避免殘留 */
    public function transferToModel(int $mediaId, Model $model, CollectionName $collectionName): bool
    {
        if ($model instanceof HasMedia) {
            // mediaId 已是目前掛載的媒體（例如未換圖時前端原樣送回既有 id）：視為不變更，不做任何動作。
            // 若不擋下這個情境，下面的 clearMediaCollection() 會先刪除這筆媒體，
            // 但它已不在 temporary 集合內，updateTemporaryToModel() 的轉移查詢會找不到對象而更新 0 筆，
            // 導致該媒體被刪除又沒有重新掛回，商品/model 變成沒有圖片。
            $currentMedia = $model->getFirstMedia($collectionName->value);
            if ($currentMedia && $currentMedia->id === $mediaId) {
                return true;
            }

            $model->clearMediaCollection($collectionName->value);
        }

        return (bool)$this->repository->updateTemporaryToModel(
            mediaId: $mediaId,
            modelType: $model->getMorphClass(),
            modelId: $model->getKey(),
            collectionName: $collectionName->value,
        );
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
