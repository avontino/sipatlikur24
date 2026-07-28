<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    protected $table = 'perangkat';
    protected $fillable = [
        'guru', 'tp', 'modul', 'media', 'penilaian'
    ];

    public $timestamps = true;
}
