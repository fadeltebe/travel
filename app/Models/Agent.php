<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'agent_user');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function originCargos()
    {
        return $this->hasMany(Cargo::class, 'origin_agent_id');
    }

    public function destinationCargos()
    {
        return $this->hasMany(Cargo::class, 'destination_agent_id');
    }

    public function cargoReceipts()
    {
        return $this->hasMany(CargoReceipt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
