<?php

declare(strict_types=1);

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'user_id',
        'store_id',
        'type',
        'status',
        'subtotal',
        'discount',
        'shipping_cost',
        'platform_fee',
        'tax',
        'total',
        'due_date',
        'paid_at',
        'notes',
        'metadata',
    ];

    public const TYPE_INVOICE = 'invoice';
    public const TYPE_CREDIT_NOTE = 'credit_note';
    public const TYPE_DEBIT_NOTE = 'debit_note';
    public const TYPE_PROFORMA = 'proforma';
    public const TYPE_TAX = 'tax';

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_LABELS = [
        self::STATUS_UNPAID => 'Belum Dibayar',
        self::STATUS_PAID => 'Lunas',
        self::STATUS_OVERDUE => 'Jatuh Tempo',
        self::STATUS_CANCELLED => 'Dibatalkan',
        self::STATUS_REFUNDED => 'Dikembalikan',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.invoices', 'commerce_invoices');
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber($invoice->type);
            }
        });
    }

    public static function generateInvoiceNumber(string $type = self::TYPE_INVOICE): string
    {
        $prefix = match ($type) {
            self::TYPE_CREDIT_NOTE => 'CN',
            self::TYPE_DEBIT_NOTE => 'DN',
            self::TYPE_PROFORMA => 'PRO',
            self::TYPE_TAX => 'TAX',
            default => 'INV',
        };

        return $prefix . '-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public function getRouteKeyName(): string
    {
        return 'invoice_number';
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('commerce.models.user', 'App\\Models\\User'));
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(config('finance.models.payment', 'App\\Models\\Payment'));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_INVOICE => 'Invoice',
            self::TYPE_CREDIT_NOTE => 'Credit Note',
            self::TYPE_DEBIT_NOTE => 'Debit Note',
            self::TYPE_PROFORMA => 'Proforma Invoice',
            self::TYPE_TAX => 'Faktur Pajak',
            default => ucfirst($this->type),
        };
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_UNPAID
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function markAsPaid(?string $reference = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    public function markAsRefunded(): void
    {
        $this->update(['status' => self::STATUS_REFUNDED]);
    }
}
