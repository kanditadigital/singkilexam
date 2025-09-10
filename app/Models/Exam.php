<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Question;
use App\Models\ExamSession;

class Exam extends Model
{
    protected $fillable = [
        'exam_type',
        'exam_name',
        'exam_description',
        'exam_code',
        'exam_status',
    ];

    public function examSessions()
    {
        return $this->hasMany(ExamSession::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
