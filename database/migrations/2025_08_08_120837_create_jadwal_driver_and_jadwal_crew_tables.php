<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabel pivot untuk menghubungkan jadwal dan driver
        Schema::create('jadwal_driver', function (Blueprint $table) {
            $table->foreignId('jadwal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->primary(['jadwal_id', 'driver_id']);
        });

        // Tabel pivot untuk menghubungkan jadwal dan kru
        Schema::create('jadwal_crew', function (Blueprint $table) {
            $table->foreignId('jadwal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crew_id')->constrained()->cascadeOnDelete();
            $table->primary(['jadwal_id', 'crew_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_crew');
        Schema::dropIfExists('jadwal_driver');
    }
};
