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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('payment_type')->default('booking'); // booking, cargo_cod
            $table->decimal('amount', 10, 2); // Jumlah pembayaran
            $table->string('method'); // cash, transfer, qris
            $table->string('reference_number')->nullable(); // Nomor referensi transfer/QRIS
            $table->string('paid_by')->nullable(); // Nama yang bayar
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete(); // User yang terima pembayaran
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete(); // Agen yang terima pembayaran
            $table->timestamp('paid_at'); // Waktu pembayaran
            $table->text('proof_photo')->nullable(); // Path foto bukti transfer
            $table->text('notes')->nullable(); // Catatan pembayaran
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
