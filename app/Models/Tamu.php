<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tamu extends Model
{
    protected $table='tamu'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nama','alamat','email','instansi','maksud','telp','created_at'];
}
