<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penumpang extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'nik',
        'jenis_kelamin',
        'tanggal_lahir',
        'telepon',
        'email',
        'alamat',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function tikets(): HasMany
    {
        return $this->hasMany(Tiket::class);
    }

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public function pemesanans()
    {
        return $this->belongsToMany(Pemesanan::class, 'pemesanan_penumpangs');
    }
}
