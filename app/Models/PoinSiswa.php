<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoinSiswa extends Model
{
    protected $table = 'poin_siswa';
    protected $fillable = ['siswa_id', 'kategori_poin_id', 'poin', 'pelapor', 'kejadian', 'tempat'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kategoriPoin()
    {
        return $this->belongsTo(KategoriPoin::class, 'kategori_poin_id');
    }
}
