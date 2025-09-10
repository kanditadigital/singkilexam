<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'session_number',
        'subject_id',
        'session_duration',
        'session_start_time',
        'session_end_time',
        'random_question',
        'random_answer',
        'show_result',
        'show_score',
        'session_status',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
