<?php

namespace Moe\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Store extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo',
        'banner',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'latitude',
        'longitude',
        'origin_village_code',
        'is_active',
        'is_official',
        'fee_rate',
        'rating',
        'total_sales',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_official' => 'boolean',
        'fee_rate' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('commerce.tables.stores', 'commerce_stores');
    }

    protected static function booted(): void
    {
        static::creating(function (Store $store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(config('commerce.models.user', 'App\\Models\\User'));
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, Product::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        $logo = $this->logo;
        if (empty($logo)) {
            return null;
        }
        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }
        try {
            return Storage::disk(config('filesystems.default'))->url($logo);
        } catch (\Throwable) {
            return $logo;
        }
    }

    public function getBannerUrlAttribute(): ?string
    {
        $banner = $this->banner;
        if (empty($banner)) {
            return null;
        }
        if (str_starts_with($banner, 'http://') || str_starts_with($banner, 'https://')) {
            return $banner;
        }
        try {
            return Storage::disk(config('filesystems.default'))->url($banner);
        } catch (\Throwable) {
            return $banner;
        }
    }

    public function getEffectiveFeeRate(): float
    {
        if ($this->fee_rate !== null) {
            return (float) $this->fee_rate;
        }

        return (float) setting('commerce.default_fee_rate', '10');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getBadgeAttribute(): string
    {
        if ($this->is_official) {
            return 'official';
        }

        return 'verified';
    }
}
