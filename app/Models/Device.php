<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['token', 'name', 'is_active', 'last_seen_at'];

    protected $casts = ['last_seen_at' => 'datetime'];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function latestLocation()
    {
        return $this->hasOne(Location::class)->latestOfMany();
    }
}
