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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_code')->unique()->nullable();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->index('route_id');
            $table->foreignId('bus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete(); // ← TAMBAH
            $table->index('driver_id');
            $table->date('departure_date'); // Tanggal keberangkatan
            $table->time('departure_time'); // Jam keberangkatan
            $table->date('arrival_date')->nullable(); // Tanggal tiba
            $table->time('arrival_time')->nullable(); // Jam perkiraan tiba
            $table->decimal('price', 10, 2); // Harga tiket (bisa beda dari base_price)
            $table->integer('available_seats'); // Sisa kursi tersedia
            $table->string('status')->default('scheduled'); // scheduled, departed, arrived, cancelled
            $table->index(['departure_date', 'status']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
