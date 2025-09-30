<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ExamAttempt;
use App\Models\Student;
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
        ]);
    }

    public function data(Request $request)
    {
        $branchId = $request->integer('branch_id');
        $schoolId = $request->integer('school_id');
        $statusFilter = $request->input('status');

        $totalQuestionsSub = DB::table('exam_attempt_questions')
            ->select('exam_attempt_id', DB::raw('COUNT(*) as total_questions'))
            ->groupBy('exam_attempt_id');

        $answeredQuestionsSub = DB::table('exam_attempt_questions')
            ->select('exam_attempt_id', DB::raw('COUNT(*) as answered_questions'))
            ->whereNotNull('answer')
            ->whereRaw("TRIM(answer) <> ''")
            ->groupBy('exam_attempt_id');

        $attempts = ExamAttempt::query()
            ->select([
                'exam_attempts.id',
                'exam_attempts.status',
                'exam_attempts.started_at',
                'exam_attempts.submitted_at',
                'exam_attempts.updated_at',
                'students.student_name',
                'students.student_nisn',
                'students.branch_id',
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
            ->join('students', function ($join) {
                $join->on('students.id', '=', 'exam_attempts.participant_id')
                    ->where('exam_attempts.participant_type', '=', Student::class);
            })
            ->join('schools', 'schools.id', '=', 'students.school_id')
            ->join('branches', 'branches.id', '=', 'students.branch_id')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('exam_sessions', 'exam_sessions.id', '=', 'exam_attempts.exam_session_id')
            ->leftJoin('exam_grades', 'exam_grades.exam_attempt_id', '=', 'exam_attempts.id')
            ->leftJoinSub($totalQuestionsSub, 'total_q', function ($join) {
                $join->on('total_q.exam_attempt_id', '=', 'exam_attempts.id');
            })
            ->leftJoinSub($answeredQuestionsSub, 'ans_q', function ($join) {
                $join->on('ans_q.exam_attempt_id', '=', 'exam_attempts.id');
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('students.branch_id', $branchId);
            })
            ->when($schoolId, function ($query) use ($schoolId) {
                $query->where('students.school_id', $schoolId);
            })
            ->when($statusFilter, function ($query) use ($statusFilter) {
                if ($statusFilter === 'active') {
                    $query->where('exam_attempts.status', 'in_progress');
                } elseif ($statusFilter === 'submitted') {
                    $query->where('exam_attempts.status', 'submitted');
                }
            })
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

            return [
                'attempt_id' => $row->id,
                'student_name' => $row->student_name,
                'student_nisn' => $row->student_nisn,
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
}
