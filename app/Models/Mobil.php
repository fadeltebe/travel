<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mobil extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_polisi',
        'merk',
        'model',
        'tahun',
        'kapasitas',
        'kelas',
        'fasilitas',
        'status',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function driver(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class);
    }
}
