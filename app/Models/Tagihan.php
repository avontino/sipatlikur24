<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = [
        'nis', 'nama', 'kelas', 'dana_komite', 'tagihan_lain'
    ];
}
