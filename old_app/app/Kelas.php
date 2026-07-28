<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table='ke_las';
    protected $fillable=['kelas','jumlah','jk','js','ji','ja','jd'];
}
