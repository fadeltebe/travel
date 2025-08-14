<?php

namespace App\Services;


use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Buat user baru dan hubungkan ke agen.
     */
    public function createUser(array $data, $agenId): User
    {
        return DB::transaction(function () use ($data, $agenId) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            // Hubungkan ke agen
            $user->agens()->attach($agenId);

            return $user;
        });
    }

    /**
     * Update user existing.
     */
    public function updateUser(User $user, array $data, $agenId): User
    {
        return DB::transaction(function () use ($user, $data, $agenId) {

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => !empty($data['password'])
                    ? $data['password']
                    : $user->password,
            ]);

            $user->agens()->sync([$agenId]);

            return $user;
        });
    }
}
