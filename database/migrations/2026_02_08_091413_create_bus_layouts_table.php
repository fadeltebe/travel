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
        Schema::create('bus_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama layout: "Layout Elf 15 Seat", "Layout Medium 25 Seat"
            $table->string('type')->nullable(); // Tipe: Elf, Medium, Big
            $table->integer('total_rows'); // Total baris
            $table->integer('total_columns'); // Total kolom
            $table->integer('total_seats'); // Total kursi
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_layouts');
    }
};
