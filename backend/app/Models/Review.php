<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id', 'user_id', 'service_id', 'vendor_profile_id',
        'rating', 'comment', 'images', 'vendor_reply', 'vendor_replied_at', 'is_visible',
    ];

    protected $casts = [
        'images' => 'array',
        'vendor_replied_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}
