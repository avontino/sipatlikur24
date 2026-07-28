<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tabel lama yang tidak sesuai
        Schema::dropIfExists('poin_siswa');

        // Buat ulang dengan struktur yang benar (detail per kejadian)
        Schema::create('poin_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('kategori_poin_id');
            $table->integer('poin')->default(0);
            $table->string('pelapor');
            $table->text('kejadian');
            $table->string('tempat')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('kategori_poin_id')->references('id')->on('kategori_poin')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poin_siswa');
    }
};
