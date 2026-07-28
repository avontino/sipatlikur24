<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table='siswa'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nis','nama','kelas','sakit','ijin','alpha','dispen','ib', 'ij', 'ik', 'is','ibr','ip','pelanggaran','prestasi'];
}
