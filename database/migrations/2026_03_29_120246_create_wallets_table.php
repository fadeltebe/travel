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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            // nullable() karena jika kosong, berarti ini adalah Dompet Utama (Bos)
            $table->foreignId('agent_id')->nullable()->constrained()->cascadeOnDelete();

            // Menggunakan decimal(15,2) sangat aman untuk mata uang Rupiah
            $table->decimal('balance', 15, 2)->default(0);

            $table->timestamps();

            // Memastikan 1 agen hanya punya 1 dompet (atau 1 company hanya punya 1 dompet utama)
            $table->unique(['company_id', 'agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
