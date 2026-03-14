<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'passenger_id',
        'ticket_number',
        'qr_code',
        'status',
        'scanned_at',
        'scanned_by',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    public function scannedByUser()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
