<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $table;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'amount',
        'reason',
        'status',
        'refunded_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.refunds', 'commerce_refunds');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
