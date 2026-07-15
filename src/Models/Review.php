<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'reply',
        'referred_by',
        'replied_at',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'replied_at' => 'datetime',
        'is_approved' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.reviews', 'commerce_reviews');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('commerce.models.user', 'App\\Models\\User'));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
