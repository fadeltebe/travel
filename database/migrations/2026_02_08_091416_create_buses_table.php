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
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique(); // Nomor plat: DN 1234 AB
            $table->string('brand')->nullable(); // Merek: Toyota, Isuzu
            $table->string('machine_number')->nullable(); // Nomor mesin
            $table->string('chassis_number')->nullable(); // Nomor rangka
            $table->string('name')->nullable(); // Nama bus: Harapan 01
            $table->string('type')->nullable(); // Elf, Medium, Big Bus
            $table->foreignId('bus_layout_id')->nullable()->constrained()->nullOnDelete(); // Layout kursi ✨
            $table->integer('total_seats'); // Total kursi (auto-calculate dari layout)
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
        Schema::dropIfExists('buses');
    }
};
