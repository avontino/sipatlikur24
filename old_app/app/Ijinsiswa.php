<?php

namespace App;
 
use Illuminate\Database\Eloquent\Model;

class Ijinsiswa extends Model
{
    protected $table='ijinsiswa'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nama','kelas','ketijin','oksis','okkur','okbin','okas','created_at','cekin','durasi','cekout','ket','wkt','filex'];
}