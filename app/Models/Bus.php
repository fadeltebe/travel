<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plate_number',
        'brand',
        'machine_number',
        'chassis_number',
        'name',
        'type',
        'bus_layout_id',
        'total_seats',
        'is_active',
    ];

    protected $casts = [
        'total_seats' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function seatLayout()
    {
        return $this->belongsTo(BusLayout::class, 'bus_layout_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
