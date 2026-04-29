<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // public static function booted (){
    //     static::created(function($user){
    //         $userTenant = Tenant::create([
    //             'id' => $user->id,
    //         ]);
    //         $userTenant->domains()->create([
    //             'domain' => $user->email,
    //         ]);
    //     });
    // }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'agent_id',
        'is_active',
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
            'password'          => 'hashed',
            'role'              => Role::class,
            'is_active'         => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'driver_id');
    }

    // ── Role Helpers ───────────────────────
    public function isSuperAdmin(): bool
    {
        return $this->role === Role::SuperAdmin;
    }

    public function isOwner(): bool
    {
        return $this->role === Role::Owner;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isDriver(): bool
    {
        return $this->role === Role::Driver;
    }

    public function hasRole(Role $role): bool
    {
        return $this->role === $role;
    }

    public function canViewAll(): bool
    {
        return $this->role->canViewAll();
    }
}
