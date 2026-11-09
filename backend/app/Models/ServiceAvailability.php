<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAvailability extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'date', 'status', 'notes'];

    protected $casts = ['date' => 'date'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
