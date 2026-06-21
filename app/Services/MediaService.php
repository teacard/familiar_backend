<?php

namespace App\Services;

use App\Enums\Media\CollectionName;
use App\Enums\Media\CustomProperty;
use App\Enums\Media\UploadFileType;
use App\Enums\TemporaryMedia\SystemName;
use App\Exceptions\NotFoundException;
use App\Models\Admin;
use App\Models\TemporaryMedia;
use App\Repositories\Applications\Media\MediaRepository;
use App\Repositories\Applications\TemporaryMedia\TemporaryMediaRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\AsRepositoryProxy;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
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

    /** 將指定暫存媒體改掛至後台人員的頭像集合（僅更新 DB；找不到時回 false） */
    public function transferToAdmin(int $mediaId, Admin $admin): bool
    {
        return (bool)$this->repository->updateTemporaryToModel(
            mediaId: $mediaId,
            modelType: $admin->getMorphClass(),
            modelId: $admin->id,
            collectionName: CollectionName::ADMIN->value,
        );
    }

    protected function getProxyRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
