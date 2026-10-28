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
        if (!Schema::hasTable('tka_siswa')) {
            Schema::create('tka_siswa', function (Blueprint $table) {
                $table->id();
                $table->string('nis', 30)->index();
                $table->string('nisn', 30)->nullable();
                $table->string('nama', 150);
                $table->string('kelas', 20)->index();
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
                $table->string('wali_kelas', 150)->nullable();
                $table->string('nip_wali_kelas', 50)->nullable();
                $table->string('jenis_tes', 30)->index(); // 'diagnostik', 'to_1', 'to_2', dst.
                $table->string('nama_tes', 100);          // 'Tes Diagnostik', 'Try Out 1', dst.
                $table->string('tahun_ajaran', 20)->default('2026/2027');
                $table->string('semester', 20)->default('Ganjil');
                $table->double('nilai_bindo', 8, 2)->nullable();
                $table->integer('nilai_bindo_bulat')->nullable();
                $table->string('ket_bindo', 30)->nullable(); // 'ISTIMEWA', 'BAIK', 'MEMADAI', 'KURANG'
                $table->double('nilai_mtk', 8, 2)->nullable();
                $table->integer('nilai_mtk_bulat')->nullable();
                $table->string('ket_mtk', 30)->nullable();   // 'ISTIMEWA', 'BAIK', 'MEMADAI', 'KURANG'
                $table->double('nilai_rata_rata', 8, 2)->nullable();
                $table->double('nilai_total', 8, 2)->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['nis', 'jenis_tes', 'tahun_ajaran'], 'unique_siswa_tka_tes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tka_siswa');
    }
};
