<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiGuru extends Model
{
    protected $table = 'presensi_guru';
    protected $fillable = [
        'user_id',
        'shift_id',
        'nama',
        'tanggal',
        'jam_datang',
        'jam_pulang',
        'foto_datang',
        'foto_pulang',
        'lat_datang',
        'lng_datang',
        'lat_pulang',
        'lng_pulang',
        'status_datang',
        'menit_terlambat',
        'status_pulang',
        'menit_pulang_cepat',
        'tahun_ajaran',
        'semester'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
