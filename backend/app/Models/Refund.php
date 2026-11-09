<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'order_item_id', 'payment_id', 'refund_number',
        'amount', 'reason', 'status', 'gateway_refund_id', 'admin_notes', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $date = now()->format('Ymd');
                $last = self::whereDate('created_at', today())->count() + 1;
                $refund->refund_number = 'REF-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
