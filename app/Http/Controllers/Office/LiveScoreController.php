<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Services\LiveScoreData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LiveScoreController extends Controller
{
    public function index()
    {
        return view('office.live-score.index', [
            'title' => 'Live Score Ujian',
            'branches' => Branch::orderBy('branch_name')->get(),
            'exams' => Exam::orderBy('exam_name')->get(),
            'publicLiveScoreEnabled' => SiteSetting::getBool('live_score_public_enabled'),
        ]);
    }

    public function data(Request $request)
    {
        $attempts = LiveScoreData::baseQuery($request->only(['branch_id', 'school_id', 'status', 'exam_id']))
            ->select([
                'exam_attempts.id',
                'exam_attempts.status',
                'exam_attempts.started_at',
                'exam_attempts.submitted_at',
                'exam_attempts.updated_at',
                'exam_attempts.participant_type',
                'exam_attempts.participant_id',
                'students.student_name',
                'students.student_nisn',
                'students.student_gender',
                'employees.employee_name',
                'employees.username as employee_username',
                'employees.employee_type',
                'branches.branch_name',
                'schools.id as school_id',
                'schools.school_name',
                'exams.exam_name',
                'exams.exam_code',
                'exam_sessions.session_number',
                'exam_sessions.session_duration',
                'exam_sessions.session_start_time',
                'exam_sessions.session_end_time',
                'exam_grades.score as grade_score',
                'exam_grades.answered_questions as grade_answered',
                'exam_grades.total_questions as grade_total',
                DB::raw('COALESCE(total_q.total_questions, 0) as total_questions'),
                DB::raw('COALESCE(ans_q.answered_questions, 0) as answered_questions'),
            ])
            ->orderByDesc('exam_attempts.updated_at')
            ->limit(200)
            ->get();

        $now = Carbon::now();

        $data = $attempts->map(function ($row) use ($now) {
            $totalQuestions = (int) ($row->total_questions ?: $row->grade_total ?: 0);
            $answeredQuestions = (int) ($row->answered_questions ?: $row->grade_answered ?: 0);

            if ($row->status === 'submitted' && $row->grade_total !== null) {
                $totalQuestions = (int) $row->grade_total;
                $answeredQuestions = (int) $row->grade_answered;
            }

            $progress = 0;
            if ($totalQuestions > 0) {
                $progress = round(($answeredQuestions / $totalQuestions) * 100, 2);
            }
            if ($row->status === 'submitted') {
                $progress = 100;
            }

            $startedAt = $row->started_at ? Carbon::parse($row->started_at) : null;
            $sessionEndTime = $row->session_end_time ? Carbon::parse($row->session_end_time) : null;
            $remainingSeconds = null;

            if ($row->status === 'in_progress' && $startedAt) {
                if ($sessionEndTime) {
                    $remainingSeconds = max(0, $sessionEndTime->timestamp - $now->timestamp);
                } elseif ($row->session_duration) {
                    $elapsed = $startedAt->diffInSeconds($now);
                    $remainingSeconds = max(0, ($row->session_duration * 60) - $elapsed);
                }
            }

            $score = $row->grade_score !== null ? (float) $row->grade_score : null;

            $isEmployee = $row->participant_type === Employee::class;
            $participantName = $isEmployee ? ($row->employee_name ?? '-') : ($row->student_name ?? '-');
            $participantIdentifier = $isEmployee
                ? ($row->employee_username ?? '-')
                : ($row->student_nisn ?? '-');
            $participantMeta = $isEmployee
                ? ($row->employee_type ?? '-')
                : ($row->student_gender ?? '-');
            $typeLabel = $isEmployee ? 'Guru/Staff' : 'Siswa';

            return [
                'attempt_id' => $row->id,
                'participant_type' => $row->participant_type,
                'participant_id' => $row->participant_id,
                'participant_type_label' => $typeLabel,
                'participant_name' => $participantName,
                'participant_identifier' => $participantIdentifier,
                'participant_meta' => $participantMeta,
                'branch_name' => $row->branch_name,
                'school_name' => $row->school_name,
                'exam_name' => $row->exam_name,
                'exam_code' => $row->exam_code,
                'session_number' => $row->session_number,
                'status' => $row->status,
                'progress' => $progress,
                'answered_questions' => $answeredQuestions,
                'total_questions' => $totalQuestions,
                'score' => $score,
                'started_at' => $row->started_at ? Carbon::parse($row->started_at)->toDateTimeString() : null,
                'submitted_at' => $row->submitted_at ? Carbon::parse($row->submitted_at)->toDateTimeString() : null,
                'updated_at' => $row->updated_at ? Carbon::parse($row->updated_at)->toDateTimeString() : null,
                'remaining_seconds' => $remainingSeconds,
            ];
        });

        return response()->json([
            'data' => $data,
            'generated_at' => $now->toIso8601String(),
        ]);
    }

    public function togglePublic(Request $request)
    {
        $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $enabled = $request->boolean('enabled');
        SiteSetting::set('live_score_public_enabled', $enabled);

        return response()->json([
            'enabled' => $enabled,
            'message' => $enabled
                ? 'Live score publik berhasil diaktifkan.'
                : 'Live score publik dinonaktifkan.',
        ]);
    }
}
