<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'billing_mode',
        'code',
        'email',
        'phone',
        'address',
        'logo',
        'npwp',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relasi ke semua dompet di bawah perusahaan ini
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    // Relasi khusus untuk mengambil Dompet Utama (Milik Bos)
    public function masterWallet()
    {
        return $this->hasOne(Wallet::class)->whereNull('agent_id');
    }
}
