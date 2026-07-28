<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table='jadwal'; //memberitahu laravel bahwa tablenya bernama jadwal
    protected $fillable=['id','kelas','jamke','jumlahjam','mapel','guru','materi','catatan','hari','j1','j2','j3','j4','j5','j6','j7','j8','j9','j10','j11','tahun_ajaran','semester','created_at'];
    


}

