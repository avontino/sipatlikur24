<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kasus extends Model
{
    protected $table='kasus'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['pelapor','kejadian','tempat','created_at'];
}

