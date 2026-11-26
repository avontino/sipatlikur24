<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SdmCuti extends Model
{
    protected $table = 'sdm_cuti';

    protected $fillable = [
        'pegawai_id',
        'user_id',
        'jenis_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'status',
        'disetujui_oleh',
        'catatan_pimpinan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(SdmPegawai::class, 'pegawai_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
