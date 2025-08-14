<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Jadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agen_id',
        'rute_id',
        'mobil_id',
        'kode_jadwal',
        'tanggal',
        'jam_berangkat',
        'jam_tiba_estimasi',
        'harga',
        'kursi_tersedia',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_berangkat' => 'datetime:H:i',
        'jam_tiba_estimasi' => 'datetime:H:i',
        'harga' => 'decimal:2',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function agen(): BelongsTo
    {
        return $this->belongsTo(Agen::class);
    }

    public function rute(): BelongsTo
    {
        return $this->belongsTo(Rute::class);
    }

    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class);
    }

    public function pemesanans(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    /**
     * The drivers that belong to the jadwal.
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'jadwal_driver');
    }

    /**
     * The crews that belong to the jadwal.
     */
    public function crews(): BelongsToMany
    {
        return $this->belongsToMany(Crew::class, 'jadwal_crew');
    }
}
