<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusLayout extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'total_rows',
        'total_columns',
        'total_seats',
        'description',
        'is_active',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'total_columns' => 'integer',
        'total_seats' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function seats()
    {
        return $this->hasMany(BusLayoutSeat::class);
    }

    public function buses()
    {
        return $this->hasMany(Bus::class, 'bus_layout_id');
    }
}
