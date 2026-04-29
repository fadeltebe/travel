<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize legacy value before tightening enum values.
        DB::statement("UPDATE passengers SET passenger_type = 'balita' WHERE passenger_type = 'anak-anak'");
        DB::statement("ALTER TABLE passengers MODIFY passenger_type ENUM('bayi','balita','dewasa') NOT NULL DEFAULT 'dewasa'");
    }

    public function down(): void
    {
        // Map bayi back to anak-anak for legacy schema compatibility.
        DB::statement("UPDATE passengers SET passenger_type = 'anak-anak' WHERE passenger_type = 'bayi'");
        DB::statement("ALTER TABLE passengers MODIFY passenger_type ENUM('balita','anak-anak','dewasa') NOT NULL DEFAULT 'dewasa'");
    }
};
