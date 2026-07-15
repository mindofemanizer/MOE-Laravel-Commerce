<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $table;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.product_variants', 'commerce_product_variants');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
