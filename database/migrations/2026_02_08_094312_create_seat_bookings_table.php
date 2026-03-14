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
        Schema::create('seat_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bus_layout_seat_id')->constrained()->cascadeOnDelete();
            $table->string('seat_number'); // Cache dari bus_layout_seats
            $table->string('status')->default('booked'); // booked, occupied, cancelled
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_bookings');
    }
};
