<?php

// app/Models/Travel.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Travel extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'email',
        'deskripsi',
        'is_active',
    ];

    protected $table = 'travels';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function agens(): HasMany
    {
        return $this->hasMany(Agen::class);
    }

    public function mobils(): HasMany
    {
        return $this->hasMany(Mobil::class);
    }

    public function rutes(): HasMany
    {
        return $this->hasMany(Rute::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function pemesanans(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    public function tikets(): HasMany
    {
        return $this->hasMany(Tiket::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
