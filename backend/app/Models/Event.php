<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'event_type', 'event_date', 'event_time',
        'location_address', 'location_city', 'location_latitude', 'location_longitude',
        'guest_count', 'budget', 'requirements', 'notes', 'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'budget' => 'decimal:2',
        'requirements' => 'array',
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
