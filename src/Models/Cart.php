<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'user_id',
        'session_id',
        'is_guest',
    ];

    protected $casts = [
        'is_guest' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.carts', 'commerce_carts');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('commerce.models.user', 'App\\Models\\User'));
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getSubtotal(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    public function getTotalItems(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
