<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'business_name', 'slug', 'description',
        'phone', 'whatsapp', 'email', 'website',
        'bank_name', 'bank_account_number', 'bank_account_holder',
        'address', 'city', 'district', 'province',
        'latitude', 'longitude', 'logo', 'banner',
        'status', 'rejection_reason', 'admin_notes', 'verified_at',
        'rating_avg', 'rating_count', 'operational_hours', 'service_area',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating_avg' => 'decimal:2',
        'verified_at' => 'datetime',
        'operational_hours' => 'array',
        'service_area' => 'array',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function advertisements()
    {
        return $this->hasMany(Advertisement::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function wallet()
    {
        return $this->hasOne(VendorWallet::class);
    }

    /**
     * Get or create wallet for this vendor
     */
    public function getOrCreateWallet(): VendorWallet
    {
        return $this->wallet ?? $this->wallet()->create([
            'balance' => 0,
            'pending_balance' => 0,
            'total_earned' => 0,
        ]);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get logo full URL accessor
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!empty($this->logo)) {
            if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                return $this->logo;
            }
            return asset('storage/' . $this->logo);
        }

        if ($this->user?->avatar) {
            return $this->user->avatar_url;
        }

        return null;
    }
}
