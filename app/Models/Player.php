<?php

namespace App\Models;

use App\Enums\Media\CollectionName;
use App\Enums\Player\Status;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Player extends Authenticatable implements HasMedia
{
    use HasFactory;         // 測試用 factory 支援
    use InteractsWithMedia; // Spatie media library 媒體綁定（頭像）
    use SoftDeletes;        // 軟刪除（保留玩家資料/歷史）

    protected $fillable = ['player_number', 'name', 'email', 'phone', 'status', 'password'];

    protected $hidden = ['password'];

    /** 註冊玩家媒體集合：單檔覆蓋（選填，目前存放頭像；無頭像時由 API response 回傳預設頭像） */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(CollectionName::PLAYER->value)
            ->singleFile();
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => Status::class,
        ];
    }

    protected static function newFactory(): PlayerFactory
    {
        return PlayerFactory::new();
    }
}
