<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ijin extends Model
{
    protected $table='ijin';
    protected $fillable = [
        'tglmasuk', 'guru', 'mapel', 'sia', 'jumlah', 'jam_terlambat', 'jam_keluar', 'jam_kembali', 'ket',
        'created_at', 'user_id', 'approval_status', 'attachment', 'tahun_ajaran', 'semester'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}