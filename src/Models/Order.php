<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Moe\Commerce\Contracts\OrderInterface;

class Order extends Model implements OrderInterface
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'order_number',
        'user_id',
        'store_id',
        'status',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount',
        'shipping_cost',
        'platform_fee',
        'total',
        'notes',
        'shipping_address_snapshot',
        'shipping_courier',
        'shipping_service',
        'shipping_etd',
        'delivered_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address_snapshot' => 'array',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const STATUS_FLOW = ['pending', 'processing', 'shipping', 'delivered', 'completed'];

    public const STATUS_LABELS = [
        'pending' => 'Menunggu Pembayaran',
        'processing' => 'Diproses',
        'shipping' => 'Dikirim',
        'delivered' => 'Terkirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'refunded' => 'Dikembalikan',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'Belum Bayar',
        'paid' => 'Dibayar',
        'verified' => 'Terverifikasi',
        'failed' => 'Gagal',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.orders', 'commerce_orders');
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('commerce.models.user', 'App\\Models\\User'));
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    // OrderInterface
    public function getOrderNumber(): string
    {
        return $this->order_number;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTotal(): float
    {
        return (float) $this->total;
    }

    /**
     * Check if the order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if the order can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['delivered', 'completed']) && ! $this->refunds()->whereIn('status', ['pending', 'approved'])->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed', 'delivered' => 'bg-secondary-container text-on-secondary-container',
            'processing', 'shipping' => 'bg-primary-container/20 text-primary',
            'cancelled', 'refunded' => 'bg-tertiary-container text-on-tertiary-container',
            'pending' => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
            default => 'bg-surface-variant text-on-surface-variant',
        };
    }
}
