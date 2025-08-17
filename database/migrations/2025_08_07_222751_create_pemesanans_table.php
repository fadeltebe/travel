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
        Schema::create('pemesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agen_id')->constrained('agens')->onDelete('cascade');
            $table->string('kode_pemesanan')->unique();
            $table->foreignId('pemesan_id')->constrained('pemesans')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->constrained()->cascadeOnDelete();
            $table->integer('jumlah_penumpang');
            $table->decimal('total_harga', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('expired_at')->nullable();
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
        Schema::dropIfExists('pemesanans');
    }
};
