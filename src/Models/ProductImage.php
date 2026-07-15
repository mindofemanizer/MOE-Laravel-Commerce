<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $table;

    protected $fillable = [
        'product_id',
        'image_url',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.product_images', 'commerce_product_images');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
