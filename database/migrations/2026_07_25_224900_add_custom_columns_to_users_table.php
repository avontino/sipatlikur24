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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'needs_password_change')) {
                $table->boolean('needs_password_change')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'tidakhadir')) {
                $table->string('tidakhadir')->nullable()->after('needs_password_change');
            }
            if (!Schema::hasColumn('users', 'walikelas_kelas')) {
                $table->string('walikelas_kelas')->nullable()->after('tidakhadir');
            }
            if (!Schema::hasColumn('users', 'additional_roles')) {
                $table->text('additional_roles')->nullable()->after('walikelas_kelas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'needs_password_change')) $columnsToDrop[] = 'needs_password_change';
            if (Schema::hasColumn('users', 'tidakhadir')) $columnsToDrop[] = 'tidakhadir';
            if (Schema::hasColumn('users', 'walikelas_kelas')) $columnsToDrop[] = 'walikelas_kelas';
            if (Schema::hasColumn('users', 'additional_roles')) $columnsToDrop[] = 'additional_roles';
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
