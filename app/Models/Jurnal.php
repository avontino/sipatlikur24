<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table='jurnal'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['id','kelas','ket_guru_mapel','penugasan','jamke','jumlahjam','mapel','guru','absen','dispen','materi','catatan','waktu','guru_id','created_at','updated_at','tahun_ajaran','semester'];
    


}

