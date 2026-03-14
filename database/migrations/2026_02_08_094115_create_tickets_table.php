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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number')->unique(); // TRV-20250208-001-P1
            $table->text('qr_code')->nullable(); // QR code string/path untuk validasi ✨
            $table->string('status')->default('active'); // active, used, cancelled
            $table->timestamp('scanned_at')->nullable(); // Kapan di-scan (saat naik bus)
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete(); // User yang scan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
