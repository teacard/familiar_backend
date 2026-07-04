<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReward extends Model
{
    protected $fillable = ['product_id', 'item_id', 'quantity'];

    /** 商品 */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** 對應的道具 */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'item_id' => 'integer',
            'quantity' => 'integer',
        ];
    }
}
