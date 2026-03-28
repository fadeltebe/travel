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
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            // 1. TAMBAHAN WAJIB (Identitas Tiket & Status)
            $table->string('ticket_code')->unique(); // Contoh: TKT-2603-ABCDEF (Untuk QR Code)
            $table->enum('status', ['booked', 'boarded', 'cancelled', 'no_show'])->default('booked');

            // 2. DATA PENUMPANG
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->enum('passenger_type', ['balita', 'anak-anak', 'dewasa'])->default('dewasa');
            $table->string('id_card_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('seat_number')->nullable();
            $table->boolean('is_booker')->default(false);

            // 3. HARGA TIKET (Harga dasar kursi khusus orang ini)
            $table->decimal('ticket_price', 10, 2)->default(0);

            // 4. SHUTTLE / ANTAR JEMPUT
            $table->boolean('need_pickup')->default(false);
            $table->text('pickup_address')->nullable();
            $table->decimal('pickup_fee', 10, 2)->default(0);

            $table->boolean('need_dropoff')->default(false);
            $table->text('dropoff_address')->nullable();
            $table->decimal('dropoff_fee', 10, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // ==========================================
            // 5. PENAMBAHAN INDEX UNTUK PERFORMA
            // ==========================================
            // Membuat pencarian di halaman Livewire menjadi kilat 
            $table->index('name');
            $table->index('phone');
            // Catatan: booking_id sudah otomatis di-index oleh Laravel karena memakai constrained()
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
