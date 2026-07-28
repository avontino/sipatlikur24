<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGarjasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('garjas', function (Blueprint $table) {
            // Tambah kolom yang diperlukan jika belum ada
            if (!Schema::hasColumn('garjas', 'nis')) {
                $table->string('nis', 20)->after('id')->index();
            }
            
            if (!Schema::hasColumn('garjas', 'bulan')) {
                $table->tinyInteger('bulan')->after('kelas')->comment('Bulan 1-12');
            }
            
            if (!Schema::hasColumn('garjas', 'tahun')) {
                $table->year('tahun')->after('bulan');
            }

            // Ubah tipe data jika diperlukan
            if (Schema::hasColumn('garjas', 'lari')) {
                $table->integer('lari')->nullable()->change();
            }
            
            if (Schema::hasColumn('garjas', 'shuttle')) {
                $table->decimal('shuttle', 8, 2)->nullable()->change();
            }
        });

        // Tambah index setelah kolom dibuat
        Schema::table('garjas', function (Blueprint $table) {
            // Tambah index untuk performa
            if (!$this->indexExists('garjas', ['bulan', 'tahun'])) {
                $table->index(['bulan', 'tahun']);
            }
            
            if (!$this->indexExists('garjas', ['kelas', 'bulan', 'tahun'])) {
                $table->index(['kelas', 'bulan', 'tahun']);
            }
            
            if (!$this->indexExists('garjas', 'unique_nis_period')) {
                $table->unique(['nis', 'bulan', 'tahun'], 'unique_nis_period');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('garjas', function (Blueprint $table) {
            if ($this->indexExists('garjas', ['bulan', 'tahun'])) {
                $table->dropIndex(['bulan', 'tahun']);
            }
            
            if ($this->indexExists('garjas', ['kelas', 'bulan', 'tahun'])) {
                $table->dropIndex(['kelas', 'bulan', 'tahun']);
            }
            
            if ($this->indexExists('garjas', 'unique_nis_period')) {
                $table->dropUnique('unique_nis_period');
            }
            
            if (Schema::hasColumn('garjas', 'nis')) {
                $table->dropColumn('nis');
            }
            
            if (Schema::hasColumn('garjas', 'bulan')) {
                $table->dropColumn('bulan');
            }
            
            if (Schema::hasColumn('garjas', 'tahun')) {
                $table->dropColumn('tahun');
            }
        });
    }

    /**
     * Helper method to check if index exists
     */
    private function indexExists($table, $index)
    {
        $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table);
        
        if (is_array($index)) {
            $indexName = $table . '_' . implode('_', $index) . '_index';
        } else {
            $indexName = $index;
        }
        
        return array_key_exists($indexName, $indexes);
    }
}