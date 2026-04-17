<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan indexing pada tabel-tabel yang sering diakses untuk meningkatkan performa query.
     * Ini adalah implementasi Tahap 1: Optimasi Database dari catatan.md
     */
    public function up(): void
    {
        // ================================================
        // CATATAN: Beberapa index sudah ada dari migration sebelumnya.
        // Hanya tambahkan index yang BELUM ada untuk mencegah error duplicate.
        // ================================================

        // ================================================
        // 3. INDEX TABEL PASSENGERS - Tambah index BARU
        // ================================================
        Schema::table('passengers', function (Blueprint $table) {
            // Index untuk filtering berdasarkan status penumpang (BARU)
            if (!$this->indexExists('passengers', 'passengers_status_index')) {
                $table->index('status');
            }
        });

        // ================================================
        // 4. INDEX TABEL CARGOS - Tambah index BARU
        // ================================================
        Schema::table('cargos', function (Blueprint $table) {
            // Index untuk filtering berdasarkan status cargo (BARU)
            if (!$this->indexExists('cargos', 'cargos_status_index')) {
                $table->index('status');
            }

            // Index composite untuk payment tracking (BARU)
            if (!$this->indexExists('cargos', 'cargos_payment_type_is_paid_index')) {
                $table->index(['payment_type', 'is_paid']);
            }
        });

        // ================================================
        // 5. INDEX TABEL ROUTES - Tambah index BARU
        // ================================================
        Schema::table('routes', function (Blueprint $table) {
            // Index untuk filtering rute aktif (BARU)
            if (!$this->indexExists('routes', 'routes_is_active_index')) {
                $table->index('is_active');
            }
        });

        // ================================================
        // 6. INDEX TABEL SEAT_BOOKINGS - Tambah index BARU
        // ================================================
        Schema::table('seat_bookings', function (Blueprint $table) {
            // Index composite untuk mencari kursi berdasarkan jadwal dan status (BARU)
            if (!$this->indexExists('seat_bookings', 'seat_bookings_schedule_id_status_index')) {
                $table->index(['schedule_id', 'status']);
            }
        });

        // ================================================
        // 7. INDEX TABEL AGENTS - Tambah jika ada
        // ================================================
        if (Schema::hasTable('agents') && Schema::hasColumn('agents', 'company_id')) {
            Schema::table('agents', function (Blueprint $table) {
                if (!$this->indexExists('agents', 'agents_company_id_index')) {
                    $table->index('company_id');
                }
            });
        }

        // ================================================
        // 8. INDEX TABEL USERS - Tambah jika ada
        // ================================================
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'agent_id')) {
            Schema::table('users', function (Blueprint $table) {
                if (!$this->indexExists('users', 'users_agent_id_index')) {
                    $table->index('agent_id');
                }
            });
        }
    }

    /**
     * Helper method untuk check apakah index sudah ada
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ================================================
        // DROP INDEXES YANG DITAMBAHKAN
        // ================================================

        // Drop dari passengers
        Schema::table('passengers', function (Blueprint $table) {
            if ($this->indexExists('passengers', 'passengers_status_index')) {
                $table->dropIndex('passengers_status_index');
            }
        });

        // Drop dari cargos
        Schema::table('cargos', function (Blueprint $table) {
            if ($this->indexExists('cargos', 'cargos_status_index')) {
                $table->dropIndex('cargos_status_index');
            }
            if ($this->indexExists('cargos', 'cargos_payment_type_is_paid_index')) {
                $table->dropIndex('cargos_payment_type_is_paid_index');
            }
        });

        // Drop dari routes
        Schema::table('routes', function (Blueprint $table) {
            if ($this->indexExists('routes', 'routes_is_active_index')) {
                $table->dropIndex('routes_is_active_index');
            }
        });

        // Drop dari seat_bookings
        Schema::table('seat_bookings', function (Blueprint $table) {
            if ($this->indexExists('seat_bookings', 'seat_bookings_schedule_id_status_index')) {
                $table->dropIndex('seat_bookings_schedule_id_status_index');
            }
        });

        // Drop dari agents jika ada
        if (Schema::hasTable('agents')) {
            Schema::table('agents', function (Blueprint $table) {
                if ($this->indexExists('agents', 'agents_company_id_index')) {
                    $table->dropIndex('agents_company_id_index');
                }
            });
        }

        // Drop dari users jika ada
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->indexExists('users', 'users_agent_id_index')) {
                    $table->dropIndex('users_agent_id_index');
                }
            });
        }
    }
};
