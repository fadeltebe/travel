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
        Schema::create('mobils', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_polisi')->unique();
            $table->string('nomor_mesin')->unique();
            $table->string('nomor_rangka')->unique();
            $table->integer('tahun_perakitan')->nullable();
            $table->integer('tahun_perolehan')->nullable();
            $table->string('merk');
            $table->string('model');
            $table->string('warna');
            $table->integer('kapasitas');
            $table->enum('tipe', ['Bus', 'Mini Bus', 'SUV', 'MPV']);
            $table->enum('kelas', ['Ekonomi', 'Bisnis', 'Sleeper', 'Executive']);
            $table->text('fasilitas')->nullable();
            $table->enum('status', ['Aktif', 'Maintenance', 'Nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobils');
    }
};
