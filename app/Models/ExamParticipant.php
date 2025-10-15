<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Exam;
use App\Models\School;

class ExamParticipant extends Model
{
    protected $fillable = [
        'exam_id',
        'school_id',
        'participant_type',
        'participant_id',
        'exam_session_id',
        'created_by',
    ];

    protected $casts = [
        'participant_id' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(School::class, 'created_by');
    }

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function scopeForParticipant($query, string $type, int $id)
    {
        return $query->where('participant_type', $type)->where('participant_id', $id);
    }
}
