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
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('origin_agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('destination_agent_id')->constrained('agents')->cascadeOnDelete();

            // Detail barang
            $table->string('description'); // Deskripsi barang
            $table->decimal('weight_kg', 8, 2)->nullable(); // Berat dalam KG
            $table->integer('quantity')->default(1); // Jumlah barang/koli
            $table->decimal('fee', 10, 2); // Biaya pengiriman

            // Data penerima
            $table->string('recipient_name')->nullable(); // Nama penerima
            $table->string('recipient_phone')->nullable(); // Telp penerima

            // Pickup & Dropoff ✨
            $table->text('pickup_address')->nullable(); // Alamat ambil barang
            $table->text('dropoff_address')->nullable(); // Alamat turun barang
            $table->string('dropoff_location_name')->nullable(); // Label lokasi: "Desa Bambaru"
            $table->decimal('pickup_fee', 10, 2)->default(0); // Biaya jemput
            $table->decimal('dropoff_fee', 10, 2)->default(0); // Biaya antar/turun
            $table->boolean('need_pickup')->default(false);
            $table->boolean('need_dropoff')->default(false);

            // Pembayaran ✨
            $table->string('payment_type')->default('paid_origin'); // paid_origin, paid_destination
            $table->string('payment_method')->nullable(); // cash, transfer, qris
            $table->boolean('is_paid')->default(false); // Status pembayaran
            $table->timestamp('paid_at')->nullable(); // Waktu pembayaran (untuk COD)

            $table->string('status')->default('pending'); // pending, in_transit, arrived, received
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
