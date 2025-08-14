<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agen;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AgenUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = DB::table('admins')->get();

        foreach ($admins as $admin) {
            DB::table('agen_user')->insert([
                'agen_id' => $admin->agen_id,
                'user_id' => $admin->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
