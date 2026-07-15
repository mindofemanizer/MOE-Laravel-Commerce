<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'discount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.order_items', 'commerce_order_items');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
