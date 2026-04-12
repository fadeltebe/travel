<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\ScheduleAccessScope;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'schedule_code',
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

    /**
     * The "booted" method of the model.
     * Otomatis dijalankan Laravel saat model Schedule dipanggil.
     */
    protected static function booted(): void
    {
        // Aktifkan kacamata filter otomatis!
        static::addGlobalScope(new ScheduleAccessScope);

        // Auto generate schedule code saat creating
        static::creating(function ($schedule) {
            if (empty($schedule->schedule_code)) {
                $datePart = \Carbon\Carbon::now()->format('ymd');
                $countToday = static::whereDate('created_at', today())->count() + 1;
                $countPart = str_pad($countToday, 3, '0', STR_PAD_LEFT);
                $schedule->schedule_code = 'JDW' . $datePart . $countPart;
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'schedule_code';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('schedule_code', $value)->orWhere('id', $value)->firstOrFail();
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

    public function bookingSumTotalCargos()
    {
        return $this->bookings()->with('cargos')->get()->sum(fn($b) => $b->cargos->sum('quantity'));
    }

    public function scopeFilterByRole($query)
    {
        $user = auth()->user();

        // 1. Jika SuperAdmin/Owner, jangan filter apa-apa (lihat semua)
        if ($user->canViewAll()) {
            return $query;
        }

        // 2. Jika Driver, lihat jadwal yang dia sopiri
        if ($user->isDriver()) {
            return $query->where('driver_id', $user->id);
        }

        // 3. Jika Admin Agen, lihat jadwal yang Asal-nya ATAU Tujuan-nya adalah agen dia
        return $query->whereHas('route', function ($q) use ($user) {
            $q->where('origin_agent_id', $user->agent_id)
                ->orWhere('destination_agent_id', $user->agent_id);
        });
    }
}
