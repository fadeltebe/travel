<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use SoftDeletes;

    // ✨ Auto eager load relasi
    protected $with = ['route', 'bus'];

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
        'departure_time' => 'string', // ✨ Fix: string bukan datetime
        'arrival_time' => 'string',   // ✨ Fix: string bukan datetime
        'price' => 'decimal:2',
        'available_seats' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function bus(): BelongsTo
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
