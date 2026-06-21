<?php

namespace App\Models;

use App\Enums\Media\CollectionName;
use App\Enums\TemporaryMedia\SystemName;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TemporaryMedia extends Model implements HasMedia
{
    use InteractsWithMedia;  // Spatie media library 媒體綁定（暫存集合）

    // protected $table = 'temporary_media';

    protected $fillable = ['system_name'];

    /** 註冊暫存媒體集合：可放多檔，未被採用者由排程定時清除 */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(CollectionName::TEMPORARY->value);
    }

    protected function casts(): array
    {
        return [
            'system_name' => SystemName::class,
        ];
    }
}
