<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shifts';

    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_pulang',
        'is_overnight',
        'toleransi_terlambat',
        'keterangan'
    ];

    protected $casts = [
        'is_overnight' => 'boolean',
        'toleransi_terlambat' => 'integer'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'default_shift_id');
    }

    public function schedules()
    {
        return $this->hasMany(UserShiftSchedule::class, 'shift_id');
    }

    public function presensiGurus()
    {
        return $this->hasMany(PresensiGuru::class, 'shift_id');
    }
}
