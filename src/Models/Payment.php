<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table;

    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'status',
        'transaction_id',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.payments', 'commerce_payments');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
