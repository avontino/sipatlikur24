<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jrekap extends Model
{
    protected $table='jrekap'; //memberitahu laravel bahwa tablenya bernama jadwal
    protected $fillable=['id','kelas','j1','j2','j3','j4','j5','j6','j7','j8','j9','j10','j11','created_at'];
    


}