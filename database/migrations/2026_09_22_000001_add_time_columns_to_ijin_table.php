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
        if (Schema::hasTable('ijin')) {
            Schema::table('ijin', function (Blueprint $table) {
                if (!Schema::hasColumn('ijin', 'jam_keluar')) {
                    $table->time('jam_keluar')->nullable()->after('jam_terlambat');
                }
                if (!Schema::hasColumn('ijin', 'jam_kembali')) {
                    $table->time('jam_kembali')->nullable()->after('jam_keluar');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ijin')) {
            Schema::table('ijin', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('ijin', 'jam_keluar')) $cols[] = 'jam_keluar';
                if (Schema::hasColumn('ijin', 'jam_kembali')) $cols[] = 'jam_kembali';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
