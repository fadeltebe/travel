<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'company_id',
        'agent_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
