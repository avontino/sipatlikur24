<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    protected $table='absen'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nama','kelas','ket','created_at','tahun_ajaran','semester'];
}