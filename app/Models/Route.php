<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'origin_city',
        'destination_city',
        'distance_km',
        'estimated_duration_minutes',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'distance_km' => 'integer',
        'estimated_duration_minutes' => 'integer',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
