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
        'nik',
        'nomor_hp',
        'foto',
        'alamat',
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
