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
            $table->string('schedule_code')->unique()->nullable()->after('id');
        });

        // Backfill existing schedules
        $schedules = DB::table('schedules')->whereNull('schedule_code')->get();
        foreach ($schedules as $schedule) {
            $datePart = \Carbon\Carbon::parse($schedule->created_at)->format('ymd');
            $countPart = str_pad($schedule->id, 3, '0', STR_PAD_LEFT);
            DB::table('schedules')
                ->where('id', $schedule->id)
                ->update(['schedule_code' => 'JDW' . $datePart . $countPart]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('schedule_code');
        });
    }
};
