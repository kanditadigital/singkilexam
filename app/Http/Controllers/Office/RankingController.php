<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\School;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RankingController extends Controller
{
    public function index()
    {
        return view('office.ranking.index', [
            'title' => 'Perengkingan Hasil Ujian',
            'exams' => Exam::orderBy('exam_name')->get(),
            'branches' => Branch::orderBy('branch_name')->get(),
        ]);
    }

    public function data(Request $request)
    {
        $this->validateRequest($request, false);

        $items = $this->collectRanking($request);
        $total = $items->count();

        $summary = [
            'total' => $total,
            'best_score' => $total ? round($items->max('score'), 2) : null,
            'average_score' => $total ? round($items->avg('score'), 2) : null,
        ];

        return response()->json([
            'data' => $items->values(),
            'summary' => $summary,
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $this->validateRequest($request, true);

        $items = $this->collectRanking($request, true);
        if ($items->isEmpty()) {
            return back()->with('error', 'Data ranking tidak ditemukan untuk filter yang dipilih.');
        }

        $exam = Exam::find($request->integer('exam_id'));
        $branch = $request->filled('branch_id') ? Branch::find($request->integer('branch_id')) : null;
        $school = $request->filled('school_id') ? School::find($request->integer('school_id')) : null;
        $participantType = $request->input('participant_type', 'student');
        $participantLabel = $participantType === 'teacher' ? 'Guru/Staff' : 'Siswa';

        $pdf = Pdf::loadView('office.ranking.pdf', [
            'items' => $items,
            'exam' => $exam,
            'branch' => $branch,
            'school' => $school,
            'participantLabel' => $participantLabel,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'ranking-' . ($exam ? Str::slug($exam->exam_name) : 'ujian')
            . '-' . Str::slug($participantLabel)
            . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    protected function validateRequest(Request $request, bool $requireExam = false): void
    {
        $examRule = $requireExam ? 'required' : 'nullable';

        $request->validate([
            'exam_id' => [$examRule, 'integer', 'exists:exams,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'participant_type' => ['nullable', 'in:student,teacher'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);
    }

    protected function collectRanking(Request $request, bool $forPdf = false): Collection
    {
        $examId = $request->integer('exam_id');
        if (!$examId) {
            return collect();
        }

        $branchId = $request->integer('branch_id');
        $schoolId = $request->integer('school_id');
        $participantKey = $request->input('participant_type', 'student');
        $limit = $request->integer('limit') ?: ($forPdf ? 300 : 100);
        $limit = max(1, min(500, $limit));

        $participantMapping = [
            'student' => Student::class,
            'teacher' => Employee::class,
        ];
        $participantClass = $participantMapping[$participantKey] ?? Student::class;

        $studentClass = Student::class;
        $employeeClass = Employee::class;

        $query = ExamAttempt::query()
            ->select([
                'exam_attempts.id',
                'exam_attempts.participant_id',
                'exam_attempts.participant_type',
                'exam_attempts.started_at',
                'exam_attempts.submitted_at',
                'exam_attempts.exam_session_id',
                'exam_grades.score',
                'exam_grades.correct_questions',
                'exam_grades.total_questions',
                'exam_grades.answered_questions',
                'exam_grades.duration_seconds',
                'exam_sessions.session_number',
                'schools.id as school_id',
                'schools.school_name',
                'schools.school_npsn',
                'branches.id as branch_id',
                'branches.branch_name',
                'students.student_name',
                'students.student_nisn',
                'students.student_gender',
                'employees.employee_name',
                'employees.username as employee_username',
                'employees.employee_type',
            ])
            ->join('exam_grades', 'exam_grades.exam_attempt_id', '=', 'exam_attempts.id')
            ->leftJoin('exam_sessions', 'exam_sessions.id', '=', 'exam_attempts.exam_session_id')
            ->leftJoin('exam_participants as ep', function ($join) {
                $join->on('ep.exam_id', '=', 'exam_attempts.exam_id')
                    ->on('ep.participant_type', '=', 'exam_attempts.participant_type')
                    ->on('ep.participant_id', '=', 'exam_attempts.participant_id');
            })
            ->leftJoin('schools', 'schools.id', '=', 'ep.school_id')
            ->leftJoin('branches', 'branches.id', '=', 'schools.branch_id')
            ->leftJoin('students', function ($join) use ($studentClass) {
                $join->on('students.id', '=', 'exam_attempts.participant_id')
                    ->where('exam_attempts.participant_type', '=', $studentClass);
            })
            ->leftJoin('employees', function ($join) use ($employeeClass) {
                $join->on('employees.id', '=', 'exam_attempts.participant_id')
                    ->where('exam_attempts.participant_type', '=', $employeeClass);
            })
            ->where('exam_attempts.status', 'submitted')
            ->where('exam_attempts.exam_id', $examId)
            ->where('exam_attempts.participant_type', $participantClass)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('schools.branch_id', $branchId);
            })
            ->when($schoolId, function ($query) use ($schoolId) {
                $query->where('schools.id', $schoolId);
            })
            ->orderByDesc('exam_grades.score')
            ->orderByDesc('exam_grades.correct_questions')
            ->orderByRaw('COALESCE(exam_grades.duration_seconds, 999999) asc')
            ->orderBy('exam_attempts.submitted_at')
            ->limit($limit);

        $results = $query->get();

        return $results->values()->map(function ($row, $index) use ($participantKey) {
            $score = $row->score !== null ? (float) $row->score : null;
            $correct = $row->correct_questions !== null ? (int) $row->correct_questions : null;
            $totalQuestions = $row->total_questions !== null ? (int) $row->total_questions : null;
            $answered = $row->answered_questions !== null ? (int) $row->answered_questions : null;
            $percentage = ($correct !== null && $totalQuestions) ? round(($correct / $totalQuestions) * 100, 2) : null;
            $duration = $row->duration_seconds !== null ? (int) $row->duration_seconds : null;
            $durationFormatted = $duration !== null ? $this->formatDuration($duration) : '-';
            $submittedAt = $row->submitted_at;
            $submittedFormatted = $submittedAt ? $submittedAt->format('d M Y H:i') : '-';

            $isTeacherView = $participantKey === 'teacher';
            $name = $isTeacherView ? ($row->employee_name ?? '-') : ($row->student_name ?? '-');
            $identifier = $isTeacherView ? ($row->employee_username ?? '-') : ($row->student_nisn ?? '-');
            $extraInfo = $isTeacherView ? ($row->employee_type ?? '-') : ($row->student_gender ?? '-');

            return [
                'rank' => $index + 1,
                'attempt_id' => $row->id,
                'participant_type' => $row->participant_type,
                'participant_label' => $isTeacherView ? 'Guru/Staff' : 'Siswa',
                'participant_name' => $name,
                'participant_identifier' => $identifier,
                'participant_meta' => $extraInfo,
                'school_id' => $row->school_id,
                'school_name' => $row->school_name ?? '-',
                'branch_id' => $row->branch_id,
                'branch_name' => $row->branch_name ?? '-',
                'session_number' => $row->session_number,
                'score' => $score,
                'score_formatted' => $score !== null ? number_format($score, 2) : '-',
                'correct_questions' => $correct,
                'total_questions' => $totalQuestions,
                'answered_questions' => $answered,
                'percentage' => $percentage,
                'percentage_formatted' => $percentage !== null ? number_format($percentage, 2) . '%' : '-',
                'duration_seconds' => $duration,
                'duration_formatted' => $durationFormatted,
                'submitted_at' => $submittedAt ? $submittedAt->toIso8601String() : null,
                'submitted_at_formatted' => $submittedFormatted,
            ];
        });
    }

    protected function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
