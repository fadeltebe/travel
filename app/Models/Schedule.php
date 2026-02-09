<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'route_id',
        'bus_id',
        'departure_date',
        'departure_time',
        'arrival_time',
        'price',
        'available_seats',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'departure_time' => 'time',
        'arrival_time' => 'time',
        'price' => 'decimal:2',
        'available_seats' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function seatBookings()
    {
        return $this->hasMany(SeatBooking::class);
    }
}
