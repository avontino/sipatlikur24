<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
        protected $table='gu_ru';
        protected $fillable=['guru','mapel','created_at'];
}
