<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
     protected $table = 'attendance';

     protected $fillable = [
         'user_id',
         'work_date',
         'check_in',
         'check_out',
     ];

    public function corrections()
    {
        return $this->hasMany(ShiftCorrection::class);
    }
}