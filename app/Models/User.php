<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use App\Models\Agen;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;
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
        'password',
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
        ];
    }

    public function agens(): BelongsToMany
    {
        return $this->belongsToMany(Agen::class, 'agen_user', 'user_id', 'agen_id');
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->agens;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->agens()->whereKey($tenant)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // if ($panel->getId() === 'superadmin') {
        //     return is_superadmin();
        // } elseif ($panel->getId() === 'admin') {
        //     return true; // Admin atau Owner bisa akses panel admin
        // }


        return true;
    }

    public function driver()
    {
        return $this->hasOne(\App\Models\Driver::class);
    }
}
