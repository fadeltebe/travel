<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama',
        'telepon',
        'alamat',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'jenis_sim',
        'foto',
    ];

    /**
     * The jadwals that belong to the driver.
     */
    public function jadwals(): BelongsToMany
    {
        return $this->belongsToMany(Jadwal::class, 'jadwal_driver');
    }
}
