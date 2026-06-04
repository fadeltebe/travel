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
        User::updateOrCreate(
            ['email' => 'sss@s.com'],
            [
                'name'      => 'Super Admin',
                'password'  => bcrypt('password'),
                'role'      => Role::SuperAdmin,
                'agent_id'  => null,
                'is_active' => true,
            ]
        );

        // Owner
        User::updateOrCreate(
            ['email' => 'owner@travel.com'],
            [
                'name'      => 'Owner',
                'password'  => bcrypt('password'),
                'role'      => Role::Owner,
                'agent_id'  => null,
                'is_active' => true,
            ]
        );

        // Admin per agent (Palu, Poso, Ampana = 3)
        $agents = Agent::all();
        foreach ($agents as $agent) {
            $email = "admin." . strtolower($agent->city) . "@travel.com";
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'      => "Admin {$agent->city}",
                    'password'  => bcrypt('password'),
                    'role'      => Role::Admin,
                    'agent_id'  => $agent->id,
                    'is_active' => true,
                ]
            );
        }

        // Drivers (4 drivers)
        for ($i = 1; $i <= 4; $i++) {
            User::updateOrCreate(
                ['email' => "driver$i@travel.com"],
                [
                    'name'      => "Driver $i",
                    'password'  => bcrypt('password'),
                    'role'      => Role::Driver,
                    'agent_id'  => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
