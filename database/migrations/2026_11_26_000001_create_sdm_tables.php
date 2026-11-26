<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aman untuk database production: hanya membuat tabel baru jika belum ada,
     * tidak menyentuh atau menghapus data lama.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sdm_pegawai')) {
            Schema::create('sdm_pegawai', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->enum('kategori', ['guru', 'tendik'])->default('guru');
                $table->string('nama');
                $table->string('nip', 50)->nullable()->default('-');
                $table->string('status_kepegawaian', 80)->nullable(); // Guru PNS, Guru PPPK, Guru Honorer, Tendik PPPK, dll
                $table->string('pangkat', 100)->nullable();
                $table->string('golongan', 30)->nullable()->default('-');
                $table->string('jabatan', 120)->nullable(); // Untuk tendik / jabatan fungsional
                $table->string('mapel', 120)->nullable();   // Untuk guru
                $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
                $table->date('tanggal_lahir')->nullable();
                $table->string('no_wa', 30)->nullable();
                $table->integer('beban_jp')->default(24);
                $table->text('kelas_diampu')->nullable(); // JSON array kelas, misal ["IX-A","IX-B"]
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sdm_tugas_tambahan')) {
            Schema::create('sdm_tugas_tambahan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pegawai_id')->index();
                $table->string('nama_tugas');
                $table->integer('ekuivalensi_jp')->default(2);
                $table->string('no_sk', 120)->nullable();
                $table->string('keterangan')->nullable();
                $table->string('tahun_ajaran', 30)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sdm_cuti')) {
            Schema::create('sdm_cuti', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pegawai_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('jenis_cuti', 80); // Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, dll
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->text('alasan')->nullable();
                $table->enum('status', ['Diajukan', 'Disetujui', 'Ditolak'])->default('Diajukan');
                $table->unsignedBigInteger('disetujui_oleh')->nullable();
                $table->text('catatan_pimpinan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdm_cuti');
        Schema::dropIfExists('sdm_tugas_tambahan');
        Schema::dropIfExists('sdm_pegawai');
    }
};
