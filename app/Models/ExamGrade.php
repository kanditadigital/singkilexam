<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamGrade extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'total_questions',
        'answered_questions',
        'correct_questions',
        'score',
        'duration_seconds',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }
}

