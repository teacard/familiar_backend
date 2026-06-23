<?php

namespace App\Models;

use App\Enums\Player\Status;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Player extends Authenticatable
{
    use HasFactory;     // 測試用 factory 支援
    use SoftDeletes;    // 軟刪除（保留玩家資料/歷史）

    protected $fillable = ['player_number', 'name', 'email', 'phone', 'status', 'password'];

    protected $hidden = ['password'];

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
