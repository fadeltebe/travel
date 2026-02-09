<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusLayoutSeat extends Model
{
    use SoftDeletes;

    protected $table = 'bus_layout_seats';

    protected $fillable = [
        'bus_layout_id',
        'row',
        'column',
        'seat_number',
        'type',
        'label',
        'capacity',
        'is_available',
    ];

    protected $casts = [
        'row' => 'integer',
        'column' => 'integer',
        'capacity' => 'integer',
        'is_available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function busLayout()
    {
        return $this->belongsTo(BusLayout::class);
    }

    public function seatBookings()
    {
        return $this->hasMany(SeatBooking::class, 'bus_layout_seat_id');
    }
}
