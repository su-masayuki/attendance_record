<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_id',
        'break_start',
        'break_end',
    ];

    public function correction()
    {
        return $this->belongsTo(StampCorrection::class, 'stamp_correction_id');
    }
}