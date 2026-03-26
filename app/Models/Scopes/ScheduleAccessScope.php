<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScheduleAccessScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Pastikan ada user yang login agar tidak error saat di console/terminal
        if (auth()->check()) {
            $user = auth()->user();

            // 1. Super Admin & Owner: Bebas hambatan, kembalikan query asli
            if (in_array($user->role, ['super_admin', 'owner'])) {
                return;
            }

            // 2. Admin Agen: Hanya jadwal yang rutenya DARI atau KE agennya
            if ($user->role === 'admin_agen') {
                $builder->whereHas('route', function ($query) use ($user) {
                    $query->where('origin_agent_id', $user->agent_id)
                          ->orWhere('destination_agent_id', $user->agent_id);
                });
                return;
            }

            // 3. Driver: Hanya jadwal di mana dia menjadi sopirnya
            if ($user->role === 'driver') {
                $builder->where('driver_id', $user->id);
                return;
            }
        }
    }
}
