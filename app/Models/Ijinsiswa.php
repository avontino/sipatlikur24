<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;

class Ijinsiswa extends Model
{
    protected $table='ijinsiswa'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nama','kelas','ketijin','ok_pembina','ok_kurikulum','ok_walikelas','ok_kesehatan','created_at','cekin','durasi','cekout','ket','wkt','filex'];
}