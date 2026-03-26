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
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('departure_date');
            $table->index('status');
        });

        Schema::table('cargos', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_type');
            $table->index('payment_method');
            $table->index('recipient_phone');
        });

        Schema::table('passengers', function (Blueprint $table) {
            $table->index('name');
            $table->index('phone');
            $table->index('id_card_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['id_card_number']);
        });

        Schema::table('cargos', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_type']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['recipient_phone']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['departure_date']);
            $table->dropIndex(['status']);
        });
    }
};
