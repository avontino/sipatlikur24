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
                if (!Schema::hasColumn('ijin', 'waktu_tiba')) {
                    $table->time('waktu_tiba')->nullable()->after('jam_kembali');
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
                if (Schema::hasColumn('ijin', 'waktu_tiba')) {
                    $table->dropColumn('waktu_tiba');
                }
            });
        }
    }
};
