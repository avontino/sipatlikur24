<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ijin extends Model
{
    protected $table='ijin';
    protected $fillable=['tglmasuk','guru','mapel','sia','jumlah','jam_terlambat','ket','created_at'];
}