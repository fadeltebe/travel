<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    protected $fillable = [
        'company_id',
        'agent_id',
        'invoice_number',
        'amount',
        'status',
        'payment_method',
        'snap_token',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
