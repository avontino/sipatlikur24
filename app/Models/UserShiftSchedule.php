<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShiftSchedule extends Model
{
    protected $table = 'user_shift_schedules';

    protected $fillable = [
        'user_id',
        'shift_id',
        'tanggal'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
