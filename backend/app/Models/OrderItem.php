<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'vendor_profile_id', 'service_id',
        'service_name_snapshot', 'price_snapshot', 'price_unit_snapshot',
        'quantity', 'subtotal', 'booking_date',
        'customer_notes', 'vendor_notes',
        'status', 'rejection_reason', 'vendor_responded_at',
        'started_at', 'completed_at', 'customer_confirmed_at',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'booking_date' => 'date',
        'vendor_responded_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    public function progressProofs()
    {
        return $this->hasMany(OrderProgressProof::class);
    }

    /**
     * Label status yang user-friendly
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_confirmation' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'rejected' => 'Ditolak',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Step number untuk progress tracker (1-5)
     */
    public function getStepNumberAttribute(): int
    {
        return match ($this->status) {
            'pending_confirmation' => 1,
            'confirmed' => 2,
            'rejected' => 0,
            'in_progress' => 3,
            'completed' => $this->customer_confirmed_at ? 5 : 4,
            'cancelled' => 0,
            default => 1,
        };
    }
}
