<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Crew extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'telepon',
        'alamat',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];


    public function jadwals(): BelongsToMany
    {
        return $this->belongsToMany(Jadwal::class, 'jadwal_crew');
    }
}
