<?php

namespace App\Models;

use App\Enums\Admin\Status;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens;  // Sanctum Token 發行與驗證
    use HasFactory;    // 測試用 factory 支援
    use HasRoles;      // Spatie 角色／權限綁定（guard_name='admin'）
    use SoftDeletes;   // 軟刪除（deleted_at）

    protected string $guard_name = 'admin';

    protected $fillable = ['name', 'email', 'password', 'status'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => Status::class,
            'last_login_date' => 'date',
        ];
    }

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }
}
