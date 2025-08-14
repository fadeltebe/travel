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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pemesanan_id')->constrained()->cascadeOnDelete();
            $table->string('kode_pembayaran')->unique();
            $table->decimal('jumlah', 12, 2);
            $table->enum('metode_pembayaran', ['cash', 'transfer', 'e_wallet', 'credit_card']);
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->string('referensi_eksternal')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
