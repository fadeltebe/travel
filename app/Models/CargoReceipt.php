<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CargoReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cargo_id',
        'receipt_number',
        'qr_code',
        'received_by_name',
        'received_by_phone',
        'received_at',
        'agent_id',
        'handler_user_id',
        'signature_photo',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_user_id');
    }
}
