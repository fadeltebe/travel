<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // SuperAdmin
        User::create([
            'name'      => 'Super Admin',
            'email'     => 'sss@s.com',
            'password'  => bcrypt('password'),
            'role'      => Role::SuperAdmin,
            'agent_id'  => null,
            'is_active' => true,
        ]);

        // Owner
        User::create([
            'name'      => 'Owner',
            'email'     => 'owner@sultengexpress.com',
            'password'  => bcrypt('password'),
            'role'      => Role::Owner,
            'agent_id'  => null,
            'is_active' => true,
        ]);

        // Admin per agent
        $agents = Agent::all();
        foreach ($agents as $agent) {
            User::create([
                'name'      => "Admin {$agent->city}",
                'email'     => "admin.{$agent->slug}@sultengexpress.com",
                'password'  => bcrypt('password'),
                'role'      => Role::Admin,
                'agent_id'  => $agent->id,
                'is_active' => true,
            ]);
        }

        // Driver
        User::create([
            'name'      => 'Budi Santoso',
            'email'     => 'driver1@sultengexpress.com',
            'password'  => bcrypt('password'),
            'role'      => Role::Driver,
            'agent_id'  => null,
            'is_active' => true,
        ]);
    }
}
