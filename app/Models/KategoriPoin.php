<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriPoin extends Model
{
    protected $table = 'kategori_poin';
    protected $fillable = ['nama_kategori', 'jenis', 'poin', 'deskripsi'];

    public function getNamaAttribute()
    {
        return $this->attributes['nama_kategori'] ?? $this->attributes['nama'] ?? '';
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama_kategori'] = $value;
    }

    public function kasus()
    {
        return $this->hasMany(Kasus::class, 'kategori_poin_id');
    }
}
