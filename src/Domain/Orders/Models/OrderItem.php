<?php

namespace Domain\Orders\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('order_items', key: 'id', keyType: 'string', incrementing: false)]
#[Fillable(['order_id', 'product_id', 'quantity', 'price_cents'])]
class OrderItem extends Model
{
    use HasUuids;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
