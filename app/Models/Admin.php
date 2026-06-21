<?php

namespace App\Models;

use App\Enums\Admin\Status;
use App\Enums\Media\CollectionName;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements HasMedia
{
    use HasApiTokens;        // Sanctum Token 發行與驗證
    use HasFactory;          // 測試用 factory 支援
    use HasRoles;            // Spatie 角色／權限綁定（guard_name='admin'）
    use InteractsWithMedia;  // Spatie media library 媒體綁定（頭像）

    protected string $guard_name = 'admin';

    protected $fillable = ['name', 'email', 'password', 'status', 'is_super_admin'];

    protected $hidden = ['password'];

    /** 註冊後台媒體集合：單檔覆蓋（選填，目前存放頭像；無頭像時由 API response 回傳預設頭像） */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(CollectionName::ADMIN->value)
            ->singleFile();
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => Status::class,
            'last_login_date' => 'date',
            'is_super_admin' => 'boolean',
        ];
    }

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }
}
