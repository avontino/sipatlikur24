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
                $table->string('verifikator_piket')->nullable()->after('ok_pembina');
            }
            if (!Schema::hasColumn('ijinsiswa', 'verifikator_walikelas')) {
                $table->string('verifikator_walikelas')->nullable()->after('ok_walikelas');
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
