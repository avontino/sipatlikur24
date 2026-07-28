<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['siswa', 'jurnal', 'jurnalh', 'jadwal', 'absen', 'ijinsiswa'];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    if (!Schema::hasColumn($tbl, 'tahun_ajaran')) {
                        $table->string('tahun_ajaran', 50)->nullable();
                    }
                    if (!Schema::hasColumn($tbl, 'semester')) {
                        $table->string('semester', 20)->nullable();
                    }
                });

                // Set existing data to 2025/2026 Genap
                DB::table($tbl)
                    ->whereNull('tahun_ajaran')
                    ->update([
                        'tahun_ajaran' => '2025/2026',
                        'semester' => 'Genap'
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['siswa', 'jurnal', 'jurnalh', 'jadwal', 'absen', 'ijinsiswa'];
        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    $cols = [];
                    if (Schema::hasColumn($tbl, 'tahun_ajaran')) $cols[] = 'tahun_ajaran';
                    if (Schema::hasColumn($tbl, 'semester')) $cols[] = 'semester';
                    if (!empty($cols)) {
                        $table->dropColumn($cols);
                    }
                });
            }
        }
    }
};
