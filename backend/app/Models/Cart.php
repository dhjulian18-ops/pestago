<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'event_id', 'service_id', 'booking_date',
        'quantity', 'price_snapshot', 'notes',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'booking_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->with(['primaryImage', 'vendorProfile', 'location']);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->price_snapshot * $this->quantity;
    }
}
