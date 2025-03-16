<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requests()
    {
        return $this->hasMany(StampCorrection::class, 'attendance_id');
    }

    public function stampCorrections()
    {
        return $this->hasMany(StampCorrection::class, 'user_id','user_id')
                ->whereColumn('target_date', 'date');
    }
}
