<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeatBooking extends Model
{
    use SoftDeletes;

    protected $table = 'seat_bookings';

    protected $fillable = [
        'schedule_id',
        'bus_layout_seat_id',
        'seat_code',
        'seat_type',
        'passenger_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function busLayoutSeat()
    {
        return $this->belongsTo(BusLayoutSeat::class);
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}
