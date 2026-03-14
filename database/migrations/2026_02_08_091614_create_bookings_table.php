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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique(); // TRV-20250208-001
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete(); // Agen yang input booking
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // Link ke customer

            // Data pemesan
            $table->string('booker_name'); // Nama pemesan
            $table->string('booker_phone'); // Telp pemesan
            $table->string('booker_email')->nullable(); // Email pemesan (optional)

            // Summary
            $table->integer('total_passengers')->default(0); // Jumlah penumpang
            $table->integer('total_cargo')->default(0); // Jumlah barang

            // Pricing breakdown
            $table->decimal('subtotal_price', 10, 2)->default(0); // Subtotal tiket penumpang
            $table->decimal('cargo_fee', 10, 2)->default(0); // Total biaya cargo (BAYAR DI ASAL)
            $table->decimal('cargo_cod_fee', 10, 2)->default(0); // Total biaya cargo COD (BAYAR DI TUJUAN)
            $table->decimal('pickup_dropoff_fee', 10, 2)->default(0); // Total biaya jemput/antar
            $table->decimal('total_price', 10, 2); // Total keseluruhan = subtotal + cargo_fee + pickup_dropoff_fee (TIDAK termasuk COD)

            // Payment info
            $table->string('payment_status')->default('pending'); // pending, paid, refunded
            $table->string('payment_method')->nullable(); // cash, transfer, qris
            $table->timestamp('paid_at')->nullable(); // Waktu pembayaran

            // Status & notes
            $table->text('notes')->nullable(); // Catatan booking
            $table->string('status')->default('confirmed'); // confirmed, cancelled, completed

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
