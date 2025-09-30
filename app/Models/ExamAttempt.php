<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExamAttempt extends Model
{
    protected $fillable = [
        'participant_type',
        'participant_id',
        'exam_id',
        'exam_session_id',
        'status',
        'started_at',
        'submitted_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function questions()
    {
        return $this->hasMany(ExamAttemptQuestion::class);
    }
}
