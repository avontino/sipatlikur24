<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kasus extends Model
{
    protected $table='kasus'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['siswa_id', 'kategori_poin_id', 'poin', 'pelapor', 'kejadian', 'tempat', 'created_at'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kategoriPoin()
    {
        return $this->belongsTo(KategoriPoin::class, 'kategori_poin_id');
    }
}

