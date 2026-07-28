<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    protected $table='absen'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nama','kelas','ket','created_at'];
}