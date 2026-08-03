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
        if (Schema::hasTable('ijin')) {
            Schema::table('ijin', function (Blueprint $table) {
                if (!Schema::hasColumn('ijin', 'tahun_ajaran')) {
                    $table->string('tahun_ajaran', 50)->nullable();
                }
                if (!Schema::hasColumn('ijin', 'semester')) {
                    $table->string('semester', 20)->nullable();
                }
                if (!Schema::hasColumn('ijin', 'user_id')) {
                    $table->bigInteger('user_id')->unsigned()->nullable();
                }
                if (!Schema::hasColumn('ijin', 'approval_status')) {
                    $table->string('approval_status', 20)->default('approved');
                }
                if (!Schema::hasColumn('ijin', 'attachment')) {
                    $table->string('attachment', 255)->nullable();
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
                $cols = [];
                if (Schema::hasColumn('ijin', 'tahun_ajaran')) $cols[] = 'tahun_ajaran';
                if (Schema::hasColumn('ijin', 'semester')) $cols[] = 'semester';
                if (Schema::hasColumn('ijin', 'user_id')) $cols[] = 'user_id';
                if (Schema::hasColumn('ijin', 'approval_status')) $cols[] = 'approval_status';
                if (Schema::hasColumn('ijin', 'attachment')) $cols[] = 'attachment';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
