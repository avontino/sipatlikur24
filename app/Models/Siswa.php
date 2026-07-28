<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table='siswa'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nis','nama','kelas','sakit','ijin','alpha','dispen','ib', 'ij', 'ik', 'is','ibr','ip','pelanggaran','prestasi'];

    public function user()
    {
        return $this->hasOne(User::class, 'username', 'nis');
    }

    public function kasus()
    {
        return $this->hasMany(Kasus::class, 'siswa_id');
    }

    public function poinSiswa()
    {
        return $this->hasMany(PoinSiswa::class, 'siswa_id');
    }

    public function totalPoinPelanggaran()
    {
        return $this->poinSiswa()->whereHas('kategoriPoin', function($q) {
            $q->where('jenis', 'pelanggaran');
        })->sum('poin');
    }

    public function totalPoinPrestasi()
    {
        return $this->poinSiswa()->whereHas('kategoriPoin', function($q) {
            $q->where('jenis', 'prestasi');
        })->sum('poin');
    }
}
