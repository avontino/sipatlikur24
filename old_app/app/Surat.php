<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $table='surat'; //memberitahu laravel bahwa tablenya bernama jurnal
    protected $fillable=['nosurat','institusi','perihal','kodesurat','ket','created_at'];
    protected $dates = ['tglmasuk'];
}
