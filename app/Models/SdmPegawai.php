<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SdmPegawai extends Model
{
    protected $table = 'sdm_pegawai';

    protected $fillable = [
        'user_id',
        'kategori',
        'nama',
        'nip',
        'status_kepegawaian',
        'pangkat',
        'golongan',
        'jabatan',
        'mapel',
        'jenis_kelamin',
        'tanggal_lahir',
        'no_wa',
        'beban_jp',
        'kelas_diampu',
    ];

    protected $casts = [
        'kelas_diampu' => 'array',
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tugasTambahan()
    {
        return $this->hasMany(SdmTugasTambahan::class, 'pegawai_id');
    }

    public function cuti()
    {
        return $this->hasMany(SdmCuti::class, 'pegawai_id');
    }

    /**
     * Hitung usia saat ini dalam tahun.
     */
    public function getUsiaAttribute()
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    /**
     * Batas Usia Pensiun (Guru: 60 thn, Tendik: 58 thn).
     */
    public function getBupAttribute()
    {
        return $this->kategori === 'tendik' ? 58 : 60;
    }

    /**
     * Estimasi tahun pensiun & sisa masa kerja.
     */
    public function getTahunPensiunAttribute()
    {
        if (!$this->tanggal_lahir) {
            return null;
        }
        return Carbon::parse($this->tanggal_lahir)->year + $this->bup;
    }

    public function getSisaTahunPensiunAttribute()
    {
        if (!$this->tahun_pensiun) {
            return null;
        }
        return $this->tahun_pensiun - now()->year;
    }

    /**
     * Link WhatsApp siap pakai (62...).
     */
    public function getWaCleanAttribute()
    {
        $digits = preg_replace('/\D/', '', (string) $this->no_wa);
        if (empty($digits)) {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }
        if (!str_starts_with($digits, '62')) {
            return '62' . $digits;
        }
        return $digits;
    }
}
