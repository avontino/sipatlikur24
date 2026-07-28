<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnalh extends Model
{
    // Nama tabel
    protected $table = 'jurnalh';

    // Kolom yang bisa diisi (fillable)
    protected $fillable = [
        'kelas', 'j1', 'j2', 'j3', 'j4', 'j5', 'j6', 'j7', 'j8', 'j9', 'j10', 'j11', 'created_at', 'tahun_ajaran', 'semester'
    ];

    // Nonaktifkan otomatisasi timestamps
    public $timestamps = false;
    
     // Relasi ke model Kelas (Jika ada)
     public function kelas()
     {
         return $this->belongsTo(Kelas::class, 'kelas', 'id'); // Sesuaikan dengan kolom yang sesuai
     }
}
