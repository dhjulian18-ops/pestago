<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_wallet_id', 'order_item_id', 'transaction_number',
        'type', 'amount', 'tax_amount', 'description', 'status',
        'proof_image', 'admin_notes', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($txn) {
            if (empty($txn->transaction_number)) {
                $txn->transaction_number = self::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->count() + 1;
        $num = 'TRX-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
        while (self::where('transaction_number', $num)->exists()) {
            $last++;
            $num = 'TRX-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
        }
        return $num;
    }

    public function wallet()
    {
        return $this->belongsTo(VendorWallet::class, 'vendor_wallet_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Label status yang user-friendly
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Transfer Admin',
            'completed' => 'Berhasil / Selesai',
            'failed' => 'Ditolak (Saldo Dikembalikan)',
            default => ucfirst($this->status),
        };
    }

    /**
     * Label type yang user-friendly
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'escrow_in' => 'Pembayaran Masuk (Escrow)',
            'released' => 'Saldo Dirilis',
            'refunded' => 'Pengembalian Dana',
            'withdrawn' => 'Pencairan',
            'tax_deducted' => 'Potongan Pajak',
            'platform_fee' => 'Biaya Platform',
            default => $this->type,
        };
    }
}
