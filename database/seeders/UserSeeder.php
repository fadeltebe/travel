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
            'email'     => 'owner@meganjaya.com',
            'password'  => bcrypt('password'),
            'role'      => Role::Owner,
            'agent_id'  => null,
            'is_active' => true,
        ]);

        // Admin per agent (Palu, Poso, Ampana = 3)
        $agents = Agent::all();
        foreach ($agents as $agent) {
            User::create([
                'name'      => "Admin {$agent->city}",
                'email'     => "admin." . strtolower($agent->city) . "@meganjaya.com",
                'password'  => bcrypt('password'),
                'role'      => Role::Admin,
                'agent_id'  => $agent->id,
                'is_active' => true,
            ]);
        }

        // Drivers (4 drivers)
        for ($i = 1; $i <= 4; $i++) {
            User::create([
                'name'      => "Driver $i",
                'email'     => "driver$i@meganjaya.com",
                'password'  => bcrypt('password'),
                'role'      => Role::Driver,
                'agent_id'  => null,
                'is_active' => true,
            ]);
        }
    }
}
