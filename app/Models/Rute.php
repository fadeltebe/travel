<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agen_id',
        'kode_rute',
        'kota_asal',
        'kota_tujuan',
        'jarak_km',
        'estimasi_waktu',
        'harga_dasar',
        'is_active',
    ];

    protected $casts = [
        'harga_dasar' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function agen(): BelongsTo
    {
        return $this->belongsTo(Agen::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function getRuteAttribute(): string
    {
        return "{$this->kota_asal} - {$this->kota_tujuan}";
    }
}
