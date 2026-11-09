<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_admin',
        'vendor_profile_id',
        'title',
        'poster_image',
        'placement',
        'show_overlay_text',
        'button_text',
        'show_badge',
        'badge_text',
        'target_url',
        'package',
        'payment_amount',
        'payment_status',
        'snap_token',
        'payment_gateway_reference',
        'payment_type',
        'paid_at',
        'review_status',
        'rejection_reason',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'payment_amount' => 'decimal:2',
        'show_overlay_text' => 'boolean',
        'show_badge' => 'boolean',
        'paid_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
}
