<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'payment_number', 'gateway', 'gateway_transaction_id',
        'gateway_reference', 'amount', 'platform_commission', 'payment_method',
        'status', 'gateway_response', 'paid_at', 'expired_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $date = now()->format('Ymd');
                $last = self::whereDate('created_at', today())->count() + 1;
                $payment->payment_number = 'PAY-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
