<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.wishlists', 'commerce_wishlists');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('commerce.models.user', 'App\\Models\\User'));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
