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
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mobil_id')->constrained()->cascadeOnDelete();
            $table->string('kode_jadwal')->unique();
            $table->date('tanggal');
            $table->time('jam_berangkat');
            $table->time('jam_tiba_estimasi');
            $table->decimal('harga', 10, 2);
            $table->integer('kursi_tersedia');
            $table->enum('status', ['Dijadwalkan', 'Berangkat', 'Tiba',  'Dibatalkan'])->default('Dijadwalkan');
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
        Schema::dropIfExists('jadwals');
    }
};
