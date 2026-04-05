<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type', // 'credit' atau 'debit'
        'amount',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    // Ini akan otomatis mengenali apakah potongannya dari Model Passenger atau Cargo
    public function reference()
    {
        return $this->morphTo();
    }
}
