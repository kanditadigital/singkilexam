<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question_option;
use App\Models\Subject;

class Question extends Model
{
    protected $fillable = [
        'subject_id',
        'question_category',
        'question_type',
        'question_format',
        'option_format',
        'question_text',
        'question_image',
        'slug',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questionOptions()
    {
        return $this->hasMany(Question_option::class);
    }
}
