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
        // 1. Table shifts
        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->string('nama_shift');
                $table->time('jam_masuk');
                $table->time('jam_pulang');
                $table->boolean('is_overnight')->default(false);
                $table->integer('toleransi_terlambat')->default(0); // menit
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add default_shift_id to users
        if (!Schema::hasColumn('users', 'default_shift_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('default_shift_id')->nullable()->after('walikelas_kelas');
            });
        }

        // 3. Table user_shift_schedules (roster harian)
        if (!Schema::hasTable('user_shift_schedules')) {
            Schema::create('user_shift_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('shift_id');
                $table->date('tanggal');
                $table->timestamps();

                $table->unique(['user_id', 'tanggal']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            });
        }

        // 4. Add shift_id to presensi_guru
        if (!Schema::hasColumn('presensi_guru', 'shift_id')) {
            Schema::table('presensi_guru', function (Blueprint $table) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('user_id');
            });
        }

        // 5. Seed default initial shifts
        $now = now();
        $initialShifts = [
            [
                'id' => 1,
                'nama_shift' => 'Reguler Guru & Tendik',
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '16:00:00',
                'is_overnight' => false,
                'toleransi_terlambat' => 0,
                'keterangan' => 'Jam kerja standar untuk Guru dan Tenaga Kependidikan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'nama_shift' => 'Shift Pagi (Satpam & Asrama)',
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '15:00:00',
                'is_overnight' => false,
                'toleransi_terlambat' => 0,
                'keterangan' => 'Shift kerja pagi jam 07.00 - 15.00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'nama_shift' => 'Shift Sore (Satpam & Asrama)',
                'jam_masuk' => '15:00:00',
                'jam_pulang' => '23:00:00',
                'is_overnight' => false,
                'toleransi_terlambat' => 0,
                'keterangan' => 'Shift kerja sore jam 15.00 - 23.00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'nama_shift' => 'Shift Malam (Satpam & Asrama)',
                'jam_masuk' => '23:00:00',
                'jam_pulang' => '07:00:00',
                'is_overnight' => true,
                'toleransi_terlambat' => 0,
                'keterangan' => 'Shift kerja malam jam 23.00 - 07.00 (melintasi tengah malam)',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        foreach ($initialShifts as $shift) {
            if (!DB::table('shifts')->where('id', $shift['id'])->exists()) {
                DB::table('shifts')->insert($shift);
            }
        }

        // Set default_shift_id = 1 for existing users if NULL
        DB::table('users')->whereNull('default_shift_id')->update(['default_shift_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('presensi_guru', 'shift_id')) {
            Schema::table('presensi_guru', function (Blueprint $table) {
                $table->dropColumn('shift_id');
            });
        }

        Schema::dropIfExists('user_shift_schedules');

        if (Schema::hasColumn('users', 'default_shift_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('default_shift_id');
            });
        }

        Schema::dropIfExists('shifts');
    }
};
