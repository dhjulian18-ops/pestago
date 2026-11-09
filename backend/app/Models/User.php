<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'admin_role_title',
        'permissions',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    // Role helpers
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        if ($this->role !== 'admin') {
            return false;
        }

        $perms = $this->permissions ?? [];
        return in_array($permission, $perms) || in_array('*', $perms);
    }

    // Relationships
    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function favoriteServices()
    {
        return $this->belongsToMany(Service::class, 'wishlists')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'customer_id');
    }

    /**
     * Get avatar full URL accessor
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!empty($this->avatar)) {
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }

        // If user is a vendor and has a vendor logo
        if ($this->role === 'vendor' && $this->vendorProfile?->logo) {
            $logo = $this->vendorProfile->logo;
            if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
                return $logo;
            }
            return asset('storage/' . $logo);
        }

        return null;
    }

    /**
     * Get count of unread incoming messages for this user (Customer, Vendor, Admin)
     */
    public function unreadMessagesCount(): int
    {
        try {
            if (in_array($this->role, ['super_admin', 'admin'])) {
                return \App\Models\Message::where('sender_id', '!=', $this->id)
                    ->where('is_read', false)
                    ->whereHas('conversation', fn($q) => $q->where('type', 'support'))
                    ->count();
            }

            if ($this->role === 'vendor') {
                $vpId = $this->vendorProfile?->id;
                if (!$vpId) return 0;
                return \App\Models\Message::where('sender_id', '!=', $this->id)
                    ->where('is_read', false)
                    ->whereHas('conversation', fn($q) => $q->where('vendor_profile_id', $vpId))
                    ->count();
            }

            // Customer
            return \App\Models\Message::where('sender_id', '!=', $this->id)
                ->where('is_read', false)
                ->whereHas('conversation', fn($q) => $q->where('customer_id', $this->id))
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
