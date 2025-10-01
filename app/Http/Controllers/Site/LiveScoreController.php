<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\School;
use App\Models\Student;
use App\Services\LiveScoreData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class LiveScoreController extends Controller
{
    public function data(Request $request)
    {
        if (! SiteSetting::getBool('live_score_public_enabled')) {
            return Response::json([
                'data' => [],
                'message' => 'Live score publik sedang nonaktif.',
            ], 403);
        }

        $attempts = LiveScoreData::baseQuery($request->only(['exam_id', 'branch_id', 'school_id']))
            ->where('exam_attempts.status', 'submitted')
            ->select([
                'exam_attempts.id',
                'exam_attempts.participant_type',
                'students.student_name',
                'employees.employee_name',
                'branches.branch_name',
                'schools.school_name',
                'exam_grades.score as grade_score',
                'exams.exam_name',
                'exams.exam_code',
                'exam_attempts.updated_at',
            ])
            ->orderByDesc('exam_attempts.updated_at')
            ->limit(100)
            ->get();

        $now = Carbon::now();

        $data = $attempts->map(function ($row) {
            $isStudent = $row->participant_type === Student::class;
            $name = $isStudent ? ($row->student_name ?? '-') : ($row->employee_name ?? '-');
            $label = $isStudent ? 'Siswa' : 'Guru/Staff';

            return [
                'participant_name' => $name,
                'participant_label' => $label,
                'branch_name' => $row->branch_name ?? '-',
                'school_name' => $row->school_name ?? '-',
                'score' => $row->grade_score !== null ? (float) $row->grade_score : null,
                'score_formatted' => $row->grade_score !== null ? number_format((float) $row->grade_score, 2) : '-',
                'exam_name' => $row->exam_name,
                'exam_code' => $row->exam_code,
                'updated_at' => $row->updated_at ? $row->updated_at->toIso8601String() : null,
            ];
        });

        return Response::json([
            'data' => $data,
            'generated_at' => $now->toIso8601String(),
        ]);
    }

    public function schoolsByBranch(int $branchId)
    {
        $schools = School::query()
            ->where('branch_id', $branchId)
            ->orderBy('school_name')
            ->select('id', 'school_name')
            ->get();

        return Response::json($schools);
    }
}
