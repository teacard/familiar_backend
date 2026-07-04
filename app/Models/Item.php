<?php

namespace App\Models;

use App\Enums\Media\CollectionName;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Item extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = ['name'];

    /** 註冊道具媒體集合：單檔覆蓋（道具圖片） */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(CollectionName::ITEM->value)
            ->singleFile();
    }

    protected static function newFactory(): ItemFactory
    {
        return ItemFactory::new();
    }
}
