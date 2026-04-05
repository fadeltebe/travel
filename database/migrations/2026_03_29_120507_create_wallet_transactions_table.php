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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            // 'credit' = Saldo bertambah (Top-Up)
            // 'debit' = Saldo berkurang (Pemotongan token untuk penumpang/kargo)
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2); // Snapshot sisa saldo setelah transaksi ini (Sangat penting untuk audit!)

            $table->string('description'); // Misal: "Potongan token untuk tiket TKT-123"

            // Polymorphic relation agar kita tahu transaksi ini dipicu oleh tabel apa (Passenger atau Cargo)
            $table->nullableMorphs('reference');

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
