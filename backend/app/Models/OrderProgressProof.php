<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProgressProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id', 'uploaded_by', 'type', 'image_path', 'caption',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Label type yang user-friendly
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'setup' => 'Persiapan / Setup',
            'in_progress' => 'Sedang Berlangsung',
            'completion' => 'Selesai Dikerjakan',
            default => $this->type,
        };
    }
}
