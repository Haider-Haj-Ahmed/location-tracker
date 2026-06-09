<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'device_id', 'latitude', 'longitude',
        'accuracy', 'speed', 'heading', 'altitude'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
