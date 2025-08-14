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
        Schema::create('tikets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pemesanan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('penumpang_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_tiket')->unique();
            $table->integer('nomor_kursi');
            $table->decimal('harga', 10, 2);
            $table->enum('status', ['active', 'used', 'cancelled'])->default('active');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tikets');
    }
};
