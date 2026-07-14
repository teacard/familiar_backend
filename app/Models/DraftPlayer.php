<?php

namespace App\Models;

use App\Enums\DraftPlayer\Step;
use App\Enums\Media\CollectionName;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DraftPlayer extends Model implements HasMedia
{
    use InteractsWithMedia; // Spatie media library 媒體綁定（大頭照直接綁在草稿本身，不經 TemporaryMedia 暫存）

    protected $fillable = [
        'email',
        'token',
        'verification_code',
        'verification_code_expires_at',
        'verification_attempts',
        'registration_step',
        'name',
    ];

    protected $hidden = ['token', 'verification_code'];

    /** 註冊草稿媒體集合：單檔覆蓋（大頭照，重新上傳自動取代舊檔；草稿刪除時 Spatie 會一併清除媒體與實體檔案） */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(CollectionName::PLAYER->value)
            ->singleFile();
    }

    protected function casts(): array
    {
        return [
            'token' => 'hashed',
            'verification_code_expires_at' => 'datetime',
            'registration_step' => Step::class,
        ];
    }
}
