<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Gunakan raw statement agar kompatibel tanpa dependensi doctrine/dbal
        DB::statement("ALTER TABLE `ijin` MODIFY `mapel` VARCHAR(191) NULL DEFAULT '-'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `ijin` MODIFY `mapel` VARCHAR(191) NOT NULL");
    }
};
