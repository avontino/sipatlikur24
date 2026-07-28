<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = [
        'nis', 'nama', 'kelas', 'dana_komite', 'tagihan_lain'
    ];
}
