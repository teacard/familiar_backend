<?php

namespace App\Models;

use App\Enums\Announcement\Status;
use App\Enums\Announcement\TargetAudience;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    /** 置頂數量限制 */
    public const TOP_LIMIT = 3;

    /** 未置頂的預設排序值 */
    public const UNPINNED_ORDER = 3;

    protected $fillable = [
        'title',
        'content',
        'status',
        'target_audience',
        'publish_at',
        'expires_at',
        'order_by',
    ];

    /**
     * 檢查是否置頂
     */
    public function getIsPinnedAttribute(): bool
    {
        return $this->order_by < self::TOP_LIMIT;
    }

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'target_audience' => TargetAudience::class,
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
            'order_by' => 'integer',
        ];
    }
}
