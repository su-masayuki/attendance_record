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

    protected $dates = [
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stampCorrections()
{
    return $this->hasMany(StampCorrection::class, 'attendance_id');
}

public function stampCorrection()
{
    return $this->hasOne(StampCorrection::class, 'attendance_id')->latest(); 
}

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getBreakTimeAttribute()
    {
        return $this->breakTimes->sum(function ($break) {
            if ($break->break_start && $break->break_end) {
                return \Carbon\Carbon::parse($break->break_end)->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
            }
            return 0;
        });
    }
}
