<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'origin_agent_id',
        'destination_agent_id',
        'description',
        'weight_kg',
        'quantity',
        'fee',
        'recipient_name',
        'recipient_phone',
        'pickup_address',
        'dropoff_address',
        'dropoff_location_name',
        'pickup_fee',
        'dropoff_fee',
        'need_pickup',
        'need_dropoff',
        'payment_type',
        'payment_method',
        'is_paid',
        'paid_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'quantity' => 'integer',
        'fee' => 'decimal:2',
        'pickup_fee' => 'decimal:2',
        'dropoff_fee' => 'decimal:2',
        'need_pickup' => 'boolean',
        'need_dropoff' => 'boolean',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function originAgent()
    {
        return $this->belongsTo(Agent::class, 'origin_agent_id');
    }

    public function destinationAgent()
    {
        return $this->belongsTo(Agent::class, 'destination_agent_id');
    }

    public function cargoReceipt()
    {
        return $this->hasOne(CargoReceipt::class);
    }
}
