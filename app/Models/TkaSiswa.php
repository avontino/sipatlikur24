<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TkaSiswa extends Model
{
    protected $table = 'tka_siswa';

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'kelas',
        'jenis_kelamin',
        'wali_kelas',
        'nip_wali_kelas',
        'jenis_tes',
        'nama_tes',
        'tahun_ajaran',
        'semester',
        'nilai_bindo',
        'nilai_bindo_bulat',
        'ket_bindo',
        'nilai_mtk',
        'nilai_mtk_bulat',
        'ket_mtk',
        'nilai_rata_rata',
        'nilai_total',
        'catatan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'nis', 'nis');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'nis', 'username');
    }

    public function getBadgeClassBindoAttribute()
    {
        return match (strtoupper((string)$this->ket_bindo)) {
            'ISTIMEWA' => 'bg-success text-white',
            'BAIK'     => 'bg-primary text-white',
            'MEMADAI'  => 'bg-warning text-dark',
            default    => 'bg-danger text-white',
        };
    }

    public function getBadgeClassMtkAttribute()
    {
        return match (strtoupper((string)$this->ket_mtk)) {
            'ISTIMEWA' => 'bg-success text-white',
            'BAIK'     => 'bg-primary text-white',
            'MEMADAI'  => 'bg-warning text-dark',
            default    => 'bg-danger text-white',
        };
    }
}
