<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemesananPenumpang extends Model
{
    protected $fillable = [
        'agen_id',
        'jadwal_id',
        'pemesanan_id',
        'penumpang_id',
        'nomor_kursi',
        'harga',
    ];

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function penumpang()
    {
        return $this->belongsTo(Penumpang::class);
    }
}
