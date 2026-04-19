<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('invoice_number')->unique(); // Misal: INV-TKN-20260329-XXXX
            $table->decimal('amount', 15, 2); // Nominal top-up

            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->string('payment_method')->nullable(); // Misal: 'qris', 'bank_transfer'
            $table->string('snap_token')->nullable(); // Untuk menyimpan token Midtrans/Xendit

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};
