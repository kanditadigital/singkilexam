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
        'subject_config',
        'break_duration',
    ];

    protected $casts = [
        'subject_config' => 'array',
    ];

    public function sessions()
    {
        return $this->hasMany(ExamSession::class, 'exam_id', 'id');
    }

    public function participants()
    {
        return $this->hasMany(ExamParticipant::class);
    }
}
