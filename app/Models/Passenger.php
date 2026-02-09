<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'name',
        'id_card_number',
        'phone',
        'seat_number',
        'is_booker',
        'pickup_address',
        'dropoff_address',
        'pickup_fee',
        'dropoff_fee',
        'need_pickup',
        'need_dropoff',
    ];

    protected $casts = [
        'is_booker' => 'boolean',
        'pickup_fee' => 'decimal:2',
        'dropoff_fee' => 'decimal:2',
        'need_pickup' => 'boolean',
        'need_dropoff' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    public function seatBooking()
    {
        return $this->hasOne(SeatBooking::class);
    }
}
