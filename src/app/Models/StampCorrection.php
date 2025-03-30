<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'target_date',
        'clock_in',
        'clock_out',
        'breaks',
        'reason',
        'status',
        'applied_at',
    ];

    protected $casts = [
        'breaks' => 'array', // JSON カラムとしてキャスト
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }

    public function breakTimes()
{
    return $this->hasMany(BreakTime::class, 'attendance_id', 'attendance_id');
}
}
