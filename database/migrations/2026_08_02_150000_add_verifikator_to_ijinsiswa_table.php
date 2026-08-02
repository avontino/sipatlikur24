<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ijinsiswa', function (Blueprint $table) {
            if (!Schema::hasColumn('ijinsiswa', 'verifikator_piket')) {
                $table->string('verifikator_piket')->nullable();
            }
            if (!Schema::hasColumn('ijinsiswa', 'verifikator_walikelas')) {
                $table->string('verifikator_walikelas')->nullable();
            }
            // Also add ok_pembina/ok_walikelas columns if they don't exist (compatibility)
            if (!Schema::hasColumn('ijinsiswa', 'ok_pembina')) {
                $table->string('ok_pembina')->default('belum')->nullable();
            }
            if (!Schema::hasColumn('ijinsiswa', 'ok_walikelas')) {
                $table->string('ok_walikelas')->default('belum')->nullable();
            }
            if (!Schema::hasColumn('ijinsiswa', 'ok_kurikulum')) {
                $table->string('ok_kurikulum')->default('belum')->nullable();
            }
            if (!Schema::hasColumn('ijinsiswa', 'ok_kesehatan')) {
                $table->string('ok_kesehatan')->default('belum')->nullable();
            }
            // Add file_path if missing
            if (!Schema::hasColumn('ijinsiswa', 'file_path')) {
                $table->string('file_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ijinsiswa', function (Blueprint $table) {
            $table->dropColumn(['verifikator_piket', 'verifikator_walikelas']);
        });
    }
};
