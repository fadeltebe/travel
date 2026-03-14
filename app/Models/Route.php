<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'origin_agent_id',
        'destination_agent_id',
        'distance_km',
        'estimated_duration_minutes',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'distance_km'                => 'integer',
            'estimated_duration_minutes' => 'integer',
            'base_price'                 => 'decimal:2',
            'is_active'                  => 'boolean',
            'created_at'                 => 'datetime',
            'updated_at'                 => 'datetime',
            'deleted_at'                 => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────
    public function originAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'origin_agent_id')
            ->withoutGlobalScopes();
    }

    public function destinationAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'destination_agent_id')
            ->withoutGlobalScopes();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    // ── Accessor ───────────────────────────
    public function getFullNameAttribute(): string
    {
        return "{$this->originAgent->city} → {$this->destinationAgent->city}";
    }
}
