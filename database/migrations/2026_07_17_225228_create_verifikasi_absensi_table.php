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
        Schema::create('verifikasi_absensi', function (Blueprint $table) {
            $table->id();
            $table->string('kelas');
            $table->date('tanggal');
            $table->string('status'); // NIHIL, ADA_ABSEN
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpha')->default(0);
            $table->integer('dispen')->default(0);
            $table->integer('hadir')->default(0);
            $table->integer('total')->default(0);
            $table->foreignId('verified_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['kelas', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi_absensi');
    }
};
