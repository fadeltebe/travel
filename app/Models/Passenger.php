<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'ticket_code',
        'status',
        'name',
        'gender',
        'passenger_type',
        'ticket_price',
        'id_card_number',
        'phone',
        'seat_number',
        'is_booker',
        'need_pickup',
        'pickup_address',
        'pickup_fee',
        'need_dropoff',
        'dropoff_address',
        'dropoff_fee',
    ];

    protected $casts = [
        'name' => 'string',
        'gender' => 'string',
        'passenger_type' => 'string',
        'ticket_price' => 'decimal:2',
        'id_card_number' => 'string',
        'phone' => 'string',
        'seat_number' => 'string',
        'is_booker' => 'boolean',
        'pickup_address' => 'string',
        'dropoff_address' => 'string',
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

    public function seatBooking()
    {
        return $this->hasOne(SeatBooking::class);
    }
}
