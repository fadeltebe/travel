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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique(); // Nomor HP sebagai identifier utama
            $table->string('email')->unique()->nullable();
            $table->string('id_card_number')->nullable(); // NIK/KTP
            $table->text('address')->nullable();
            $table->string('password')->nullable(); // Untuk login mandiri (nanti)
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('total_bookings')->default(0); // Cache: jumlah booking
            $table->integer('total_trips')->default(0); // Cache: jumlah perjalanan
            $table->integer('total_shipments')->default(0); // Cache: jumlah pengiriman barang
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
