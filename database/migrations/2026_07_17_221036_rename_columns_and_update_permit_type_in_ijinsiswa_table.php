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
        Schema::table('ijinsiswa', function (Blueprint $table) {
            $table->renameColumn('oksis', 'ok_pembina');
            $table->renameColumn('okkur', 'ok_kurikulum');
            $table->renameColumn('okbin', 'ok_walikelas');
            $table->renameColumn('okas', 'ok_kesehatan');
        });

        // Update existing values of ketijin
        DB::table('ijinsiswa')
            ->where('ketijin', 'Ijin Bermalam Wajib')
            ->update(['ketijin' => 'Ijin Bermalam']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ijinsiswa')
            ->where('ketijin', 'Ijin Bermalam')
            ->update(['ketijin' => 'Ijin Bermalam Wajib']);

        Schema::table('ijinsiswa', function (Blueprint $table) {
            $table->renameColumn('ok_pembina', 'oksis');
            $table->renameColumn('ok_kurikulum', 'okkur');
            $table->renameColumn('ok_walikelas', 'okbin');
            $table->renameColumn('ok_kesehatan', 'okas');
        });
    }
};
