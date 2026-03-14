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
            $table->string('name'); // Nama penumpang
            $table->string('id_card_number')->nullable(); // NIK/KTP
            $table->string('phone')->nullable(); // Nomor telepon penumpang
            $table->string('seat_number')->nullable(); // Nomor kursi: A1, B2, etc
            $table->boolean('is_booker')->default(false); // Apakah dia yang pesan?
            $table->text('pickup_address')->nullable(); // Alamat jemput
            $table->text('dropoff_address')->nullable(); // Alamat tujuan/antar
            $table->decimal('pickup_fee', 10, 2)->default(0); // Biaya jemput
            $table->decimal('dropoff_fee', 10, 2)->default(0); // Biaya antar
            $table->boolean('need_pickup')->default(false);
            $table->boolean('need_dropoff')->default(false);
            $table->timestamps();
            $table->softDeletes();
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
