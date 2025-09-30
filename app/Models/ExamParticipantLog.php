<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Exam;
use App\Models\School;

class ExamParticipantLog extends Model
{
    protected $fillable = [
        'exam_id',
        'school_id',
        'participant_type',
        'participant_id',
        'performed_by',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
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

    public function performer(): BelongsTo
    {
        return $this->belongsTo(School::class, 'performed_by');
    }
}
