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
        Schema::create('bus_layout_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_layout_id')->constrained()->cascadeOnDelete();
            $table->integer('row'); // Baris: 1, 2, 3, 4, 5
            $table->integer('column'); // Kolom: 1, 2, 3
            $table->string('seat_number')->nullable(); // A1, A2, B1, atau null untuk pintu/gang
            $table->string('type'); // driver, passenger, door, aisle, long_seat
            $table->string('label')->nullable(); // Label: "Sopir", "Pintu", "Long Seat"
            $table->integer('capacity')->default(1); // 1 untuk kursi biasa, 3 untuk long seat
            $table->boolean('is_available')->default(true); // Bisa dibooking atau tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_layout_seats');
    }
};
