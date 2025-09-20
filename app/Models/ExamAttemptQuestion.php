<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttemptQuestion extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'order_index',
        'flagged',
        'answer',
        'options_order',
        'answered_at',
    ];

    protected $casts = [
        'flagged' => 'boolean',
        'options_order' => 'array',
        'answered_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
