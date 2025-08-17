<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'agen_id',
        'nama',
        'alamat',
        'nik',
        'telepon',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'foto',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }
}
