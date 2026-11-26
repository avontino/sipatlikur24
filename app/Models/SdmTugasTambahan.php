<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SdmTugasTambahan extends Model
{
    protected $table = 'sdm_tugas_tambahan';

    protected $fillable = [
        'pegawai_id',
        'nama_tugas',
        'ekuivalensi_jp',
        'no_sk',
        'keterangan',
        'tahun_ajaran',
    ];

    public function pegawai()
    {
        return $this->belongsTo(SdmPegawai::class, 'pegawai_id');
    }
}
