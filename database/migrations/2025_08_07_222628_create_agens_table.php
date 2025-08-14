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
        Schema::create('agens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('kode_agen')->unique();
            $table->string('kota');
            $table->string('alamat');
            $table->string('telepon');
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('agen_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agen_id');
            $table->foreign('agen_id')->references('id')->on('agens')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first (optional but safe)
        Schema::table('agen_user', function (Blueprint $table) {
            $table->dropForeign(['agen_id']);
            $table->dropForeign(['user_id']);
        });

        // Drop child table first
        Schema::dropIfExists('agen_user');
        Schema::dropIfExists('agens');
    }
};
