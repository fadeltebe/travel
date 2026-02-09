<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_code',
        'schedule_id',
        'agent_id',
        'customer_id',
        'booker_name',
        'booker_phone',
        'booker_email',
        'total_passengers',
        'total_cargo',
        'subtotal_price',
        'cargo_fee',
        'cargo_cod_fee',
        'pickup_dropoff_fee',
        'total_price',
        'payment_status',
        'payment_method',
        'paid_at',
        'notes',
        'status',
    ];

    protected $casts = [
        'total_passengers' => 'integer',
        'total_cargo' => 'integer',
        'subtotal_price' => 'decimal:2',
        'cargo_fee' => 'decimal:2',
        'cargo_cod_fee' => 'decimal:2',
        'pickup_dropoff_fee' => 'decimal:2',
        'total_price' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    public function cargos()
    {
        return $this->hasMany(Cargo::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
