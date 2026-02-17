<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Route extends Model
{
    use SoftDeletes;

    // ✨ Auto eager load relasi setiap kali Route diquery
    protected $with = ['originAgent', 'destinationAgent'];

    protected $fillable = [
        'origin_agent_id',
        'destination_agent_id',
        'distance_km',
        'estimated_duration_minutes',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'distance_km' => 'integer',
        'estimated_duration_minutes' => 'integer',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function originAgent()
    {
        return $this->belongsTo(Agent::class, 'origin_agent_id')
            ->withoutGlobalScopes();
    }

    public function destinationAgent()
    {
        return $this->belongsTo(Agent::class, 'destination_agent_id')
            ->withoutGlobalScopes();
    }
}
