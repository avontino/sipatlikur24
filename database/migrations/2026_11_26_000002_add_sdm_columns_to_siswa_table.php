<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom pelengkap demografi siswa secara aman (nullable)
     * tanpa mengubah atau menghapus data siswa yang sudah ada.
     */
    public function up(): void
    {
        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                if (!Schema::hasColumn('siswa', 'nisn')) {
                    $table->string('nisn', 30)->nullable()->after('nis');
                }
                if (!Schema::hasColumn('siswa', 'jenis_kelamin')) {
                    $table->string('jenis_kelamin', 5)->nullable()->after('kelas');
                }
                if (!Schema::hasColumn('siswa', 'agama')) {
                    $table->string('agama', 50)->nullable()->after('jenis_kelamin');
                }
                if (!Schema::hasColumn('siswa', 'nama_ortu')) {
                    $table->string('nama_ortu')->nullable()->after('agama');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('siswa', 'nisn')) $cols[] = 'nisn';
                if (Schema::hasColumn('siswa', 'jenis_kelamin')) $cols[] = 'jenis_kelamin';
                if (Schema::hasColumn('siswa', 'agama')) $cols[] = 'agama';
                if (Schema::hasColumn('siswa', 'nama_ortu')) $cols[] = 'nama_ortu';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
