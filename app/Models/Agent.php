<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'city',
        'address',
        'phone',
        'email',
        'commission_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'commission_rate' => 'decimal:2',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
            'deleted_at'      => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function originRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'origin_agent_id');
    }

    public function destinationRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'destination_agent_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function originCargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'origin_agent_id');
    }

    public function destinationCargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'destination_agent_id');
    }

    public function cargoReceipts(): HasMany
    {
        return $this->hasMany(CargoReceipt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
