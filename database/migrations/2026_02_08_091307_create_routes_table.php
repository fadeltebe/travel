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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('origin_city'); // Kota asal
            $table->string('destination_city'); // Kota tujuan
            $table->integer('distance_km')->nullable(); // Jarak dalam KM
            $table->integer('estimated_duration_minutes')->nullable(); // Estimasi durasi dalam menit
            $table->decimal('base_price', 10, 2); // Harga dasar
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
