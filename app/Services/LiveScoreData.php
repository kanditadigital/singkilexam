<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ExamAttempt;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LiveScoreData
{
    public static function baseQuery(array $filters = []): Builder
    {
        $branchId = isset($filters['branch_id']) && $filters['branch_id'] !== '' ? (int) $filters['branch_id'] : null;
        $schoolId = isset($filters['school_id']) && $filters['school_id'] !== '' ? (int) $filters['school_id'] : null;
        $statusFilter = $filters['status'] ?? null;
        $examId = isset($filters['exam_id']) && $filters['exam_id'] !== '' ? (int) $filters['exam_id'] : null;

        $studentClass = Student::class;
        $employeeClass = Employee::class;

        $totalQuestionsSub = DB::table('exam_attempt_questions')
            ->select('exam_attempt_id', DB::raw('COUNT(*) as total_questions'))
            ->groupBy('exam_attempt_id');

        $answeredQuestionsSub = DB::table('exam_attempt_questions')
            ->select('exam_attempt_id', DB::raw('COUNT(*) as answered_questions'))
            ->whereNotNull('answer')
            ->whereRaw("TRIM(answer) <> ''")
            ->groupBy('exam_attempt_id');

        return ExamAttempt::query()
            ->leftJoin('exam_participants as ep', function ($join) {
                $join->on('ep.exam_id', '=', 'exam_attempts.exam_id')
                    ->on('ep.participant_type', '=', 'exam_attempts.participant_type')
                    ->on('ep.participant_id', '=', 'exam_attempts.participant_id');
            })
            ->leftJoin('students', function ($join) use ($studentClass) {
                $join->on('students.id', '=', 'exam_attempts.participant_id')
                    ->where('exam_attempts.participant_type', '=', $studentClass);
            })
            ->leftJoin('employees', function ($join) use ($employeeClass) {
                $join->on('employees.id', '=', 'exam_attempts.participant_id')
                    ->where('exam_attempts.participant_type', '=', $employeeClass);
            })
            ->leftJoin('schools', function ($join) {
                $join->on('schools.id', '=', 'ep.school_id');
            })
            ->leftJoin('branches', 'branches.id', '=', 'schools.branch_id')
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
                $query->where('schools.branch_id', $branchId);
            })
            ->when($schoolId, function ($query) use ($schoolId) {
                $query->where('schools.id', $schoolId);
            })
            ->when($statusFilter, function ($query) use ($statusFilter) {
                if ($statusFilter === 'active') {
                    $query->where('exam_attempts.status', 'in_progress');
                } elseif ($statusFilter === 'submitted') {
                    $query->where('exam_attempts.status', 'submitted');
                }
            })
            ->when($examId, function ($query) use ($examId) {
                $query->where('exam_attempts.exam_id', $examId);
            });
    }
}
