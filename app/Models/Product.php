<?php

namespace App\Models;

use App\Enums\Media\CollectionName;
use App\Enums\Product\Status;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;          // 測試用 factory 支援
    use InteractsWithMedia;  // Spatie media library 媒體綁定（商品主圖）

    protected $fillable = ['name', 'product_type_id', 'amount', 'status'];

    /** 註冊商品媒體集合：單檔覆蓋（商品主圖） */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(CollectionName::PRODUCT->value)
            ->singleFile();
    }

    /** 商品類別 */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /** 商品內容物 */
    public function productRewards(): HasMany
    {
        return $this->hasMany(ProductReward::class);
    }

    protected function casts(): array
    {
        return [
            'product_type_id' => 'integer',
            'amount' => 'integer',
            'status' => Status::class,
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
