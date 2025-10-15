<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;

class ExamSession extends Model
{
    protected ?array $resolvedSubjectNamesCache = null;

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
        'question_count',
        'session_subjects',
        'break_duration',
    ];

    protected $casts = [
        'session_start_time' => 'datetime',
        'session_end_time' => 'datetime',
        'session_subjects' => 'array',
        'break_duration' => 'integer',
    ];

    /**
     * Get the total duration for this session including all subjects and breaks
     */
    public function getTotalDurationAttribute()
    {
        if (!$this->session_subjects || !is_array($this->session_subjects)) {
            return $this->session_duration ?? 0;
        }

        $totalDuration = 0;
        $subjectCount = count($this->session_subjects);

        foreach ($this->session_subjects as $subjectConfig) {
            $totalDuration += $subjectConfig['duration'] ?? $this->session_duration ?? 0;
        }

        // Add break time between subjects (breaks are between subjects, so subjectCount - 1)
        if ($subjectCount > 1) {
            $totalDuration += ($subjectCount - 1) * ($this->break_duration ?? 0);
        }

        return $totalDuration;
    }

    /**
     * Resolve subject names configured for this session.
     *
     * @return array<int,string>
     */
    public function resolveSubjectNames(): array
    {
        if ($this->resolvedSubjectNamesCache !== null) {
            return $this->resolvedSubjectNamesCache;
        }

        $configs = collect($this->session_subjects ?? []);
        if ($configs->isEmpty()) {
            $name = optional($this->subject)->subject_name;
            return $this->resolvedSubjectNamesCache = $name ? [$name] : [];
        }

        $subjectIds = $configs
            ->pluck('subject_id')
            ->filter()
            ->map(static fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($subjectIds->isEmpty()) {
            return $this->resolvedSubjectNamesCache = [];
        }

        $subjectMap = Subject::whereIn('id', $subjectIds)->pluck('subject_name', 'id');

        $names = [];
        foreach ($configs as $config) {
            $subjectId = (int) ($config['subject_id'] ?? 0);
            $name = $subjectMap[$subjectId] ?? null;
            if ($name) {
                $names[] = $name;
            }
        }

        if (empty($names) && $this->subject) {
            $names[] = $this->subject->subject_name;
        }

        return $this->resolvedSubjectNamesCache = $names;
    }

    public function getSubjectDisplayNameAttribute(): string
    {
        $names = $this->resolveSubjectNames();
        return implode(', ', $names);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
