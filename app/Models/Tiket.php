<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tiket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agen_id',
        'pemesanan_id',
        'penumpang_id',
        'nomor_tiket',
        'nomor_kursi',
        'harga',
        'status',
        'check_in_at',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'check_in_at' => 'datetime',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function agen(): BelongsTo
    {
        return $this->belongsTo(Agen::class);
    }

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function penumpang(): BelongsTo
    {
        return $this->belongsTo(Penumpang::class);
    }
}
