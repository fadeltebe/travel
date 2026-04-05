<?php

// php artisan make:migration add_billing_mode_to_companies_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // 'centralized' = Bos yang bayar token
            // 'per_agent' = Masing-masing agen bayar token sendiri
            $table->enum('billing_mode', ['centralized', 'per_agent'])->default('centralized')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('billing_mode');
        });
    }
};
