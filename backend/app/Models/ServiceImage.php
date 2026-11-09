<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'image_path', 'caption', 'is_primary', 'sort_order'];

    protected $casts = ['is_primary' => 'boolean'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
