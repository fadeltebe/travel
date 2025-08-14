<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pemesanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agen_id',
        'jadwal_id',
        'kode_pemesanan',
        'nama_pemesan',
        'telepon_pemesan',
        'email_pemesan',
        'jumlah_penumpang',
        'total_harga',
        'status',
        'expired_at',
        'catatan',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'expired_at' => 'datetime',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function agen(): BelongsTo
    {
        return $this->belongsTo(Agen::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
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
