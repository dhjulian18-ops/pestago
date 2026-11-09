<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id', 'category_id', 'name', 'slug', 'description',
        'price', 'price_unit', 'price_min', 'price_max',
        'capacity_min', 'capacity_max',
        'facilities', 'requirements', 'cancellation_policy',
        'is_active', 'is_featured', 'status',
        'rating_avg', 'rating_count', 'booking_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'facilities' => 'array',
        'requirements' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'rating_avg' => 'decimal:2',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ServiceImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ServiceImage::class)->where('is_primary', true);
    }

    public function location()
    {
        return $this->hasOne(ServiceLocation::class);
    }

    public function availabilities()
    {
        return $this->hasMany(ServiceAvailability::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function isWishlistedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->wishlists()->where('user_id', $user->id)->exists();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getPriceDisplayAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getThumbnailUrlAttribute(): string
    {
        // 1. Jika vendor telah meng-upload foto utama
        if ($this->primaryImage && !empty($this->primaryImage->image_path)) {
            return asset('storage/' . $this->primaryImage->image_path);
        }

        // 2. Jika ada foto apa saja dari vendor
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return asset('storage/' . $this->images->first()->image_path);
        }

        // 3. Fallback ilustrasi bertema berdasarkan slug & kategori
        $slug = strtolower($this->slug ?? '');
        $cat = strtolower($this->category->slug ?? '');

        // Prioritaskan MUA / Makeup / Rias sebelum Dekorasi Pelaminan
        if (str_contains($slug, 'mua') || str_contains($slug, 'makeup') || str_contains($slug, 'rias') || str_contains($slug, 'beauty') || str_contains($cat, 'makeup') || str_contains($slug, 'busana') || str_contains($cat, 'busana')) {
            return asset('images/service-mua-wedding.svg');
        }
        if (str_contains($slug, 'foto') || str_contains($slug, 'video') || str_contains($cat, 'dokumentasi')) {
            return asset('images/service-foto-wedding.svg');
        }
        if (str_contains($slug, 'ballroom') || str_contains($slug, 'gedung') || str_contains($slug, 'venue') || str_contains($slug, 'hall') || str_contains($cat, 'gedung') || str_contains($cat, 'venue')) {
            return asset('images/service-ballroom.svg');
        }
        if (str_contains($slug, 'pelaminan') || str_contains($slug, 'dekorasi') || str_contains($slug, 'tenda') || str_contains($cat, 'dekorasi')) {
            return asset('images/service-pelaminan.svg');
        }
        if (str_contains($slug, 'catering') || str_contains($slug, 'prasmanan') || str_contains($slug, 'nasi') || str_contains($cat, 'catering')) {
            return asset('images/service-catering.svg');
        }
        if (str_contains($slug, 'band') || str_contains($slug, 'musik') || str_contains($slug, 'mc') || str_contains($slug, 'hiburan') || str_contains($cat, 'hiburan')) {
            return asset('images/service-liveband.svg');
        }

        return asset('images/service-mua-wedding.svg');
    }
}
