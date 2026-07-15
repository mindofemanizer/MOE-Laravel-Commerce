<?php

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Moe\Commerce\Contracts\SellableInterface;

class Product extends Model implements SellableInterface
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'store_id',
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'description',
        'sku',
        'barcode',
        'retail_price',
        'wholesale_price',
        'minimum_order',
        'unit',
        'weight',
        'is_active',
        'total_sold',
        'rating',
        'total_reviews',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'minimum_order' => 'integer',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'total_sold' => 'integer',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.products', 'commerce_products');
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(config('commerce.models.inventory', 'App\\Models\\Inventory'));
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // SellableInterface
    public function isAvailable(): bool
    {
        return (bool) $this->is_active && $this->getStock() > 0;
    }

    public function getStock(): int
    {
        return $this->inventory?->quantity ?? 0;
    }

    public function getPrice(): float
    {
        return (float) $this->retail_price;
    }

    public function getMinimumOrder(): int
    {
        return $this->minimum_order ?? 1;
    }

    public function getStoreId(): ?int
    {
        return $this->store_id;
    }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->images()->where('is_primary', true)->first()?->image_url
            ?? $this->images()->first()?->image_url;
    }
}
