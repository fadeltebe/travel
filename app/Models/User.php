<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasTenants;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->agents;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->agents()->whereKey($tenant)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function ticketsScanned()
    {
        return $this->hasMany(Ticket::class, 'scanned_by');
    }

    public function cargoReceiptsHandled()
    {
        return $this->hasMany(CargoReceipt::class, 'handler_user_id');
    }

    public function paymentsReceived()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }
}
