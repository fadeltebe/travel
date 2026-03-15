<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'route_id',
        'bus_id',
        'driver_id',           // ← TAMBAH
        'departure_date',
        'departure_time',
        'arrival_date',        // ← TAMBAH (beda hari)
        'arrival_time',
        'price',
        'available_seats',
        'status',
    ];

    protected function casts(): array  // ← update ke method
    {
        return [
            'departure_date'  => 'date',
            'arrival_date'    => 'date',   // ← TAMBAH
            'departure_time'  => 'string',
            'arrival_time'    => 'string',
            'price'           => 'decimal:2',
            'available_seats' => 'integer',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
            'deleted_at'      => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver(): BelongsTo  // ← TAMBAH
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function seatBookings(): HasMany
    {
        return $this->hasMany(SeatBooking::class);
    }

    // ── Helpers ────────────────────────────
    public function isAvailable(): bool
    {
        return $this->status === 'scheduled'
            && $this->available_seats > 0;
    }

    // Mencari total pendapatan kargo vs penumpang dalam satu jadwal
    public function getTotalCargoRevenueAttribute()
    {
        return $this->bookings()->with('cargos')->get()->sum(fn($b) => $b->cargos->sum('fee'));
    }

    public function getTotalPassengerRevenueAttribute()
    {
        return $this->bookings()->sum('total_price');
    }

    public function bookingSumTotalPassengers()
    {
        return $this->bookings()->sum('total_passengers');
    }
}
