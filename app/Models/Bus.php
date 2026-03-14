<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plate_number',
        'brand',
        'machine_number',
        'chassis_number',
        'name',
        'type',
        'bus_layout_id',
        'total_seats',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_seats' => 'integer',
            'is_active'   => 'boolean',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
            'deleted_at'  => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────
    public function busLayout(): BelongsTo
    {
        return $this->belongsTo(BusLayout::class);
        // Tidak perlu tulis 'bus_layout_id' karena Laravel auto-detect
        // dari nama method busLayout() → bus_layout_id
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
