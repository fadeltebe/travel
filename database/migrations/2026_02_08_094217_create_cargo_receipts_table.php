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
        Schema::create('cargo_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique(); // TRV-20250208-001-C1
            $table->text('qr_code')->nullable(); // QR code string/path ✨

            // Data penerima barang
            $table->string('received_by_name'); // Nama yang ambil barang
            $table->string('received_by_phone')->nullable(); // Telepon penerima
            $table->timestamp('received_at'); // Waktu penerimaan

            // Data agen & handler
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete(); // Agen yang serahkan barang
            $table->foreignId('handler_user_id')->nullable()->constrained('users')->nullOnDelete(); // User agen yang handle

            // Bukti penerimaan
            $table->text('signature_photo')->nullable(); // Path foto tanda tangan/selfie
            $table->text('notes')->nullable(); // Catatan penerimaan

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_receipts');
    }
};
