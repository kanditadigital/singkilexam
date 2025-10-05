<?php

namespace App\Http\Controllers\Cabdin;

use App\Http\Controllers\Controller;
use App\Models\ExamParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index()
    {
        $branch = Auth::guard('branches')->user();

        $schoolsCount = $branch->schools()->count();
        $activeSchools = $branch->schools()->where('is_active', true)->count();
        $studentsCount = $branch->students()->count();

        return view('cabdin.dashboard', [
            'title' => 'Dashboard Cabdin',
            'branch' => $branch,
            'schoolsCount' => $schoolsCount,
            'activeSchools' => $activeSchools,
            'studentsCount' => $studentsCount,
        ]);
    }

    public function examParticipants(Request $request)
    {
        $branch = Auth::guard('branches')->user();

        if ($request->ajax()) {
            $participantsQuery = ExamParticipant::with(['school', 'exam.sessions.subject', 'participant'])
                ->whereHas('school', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                });

            // Filter by school if provided
            if ($request->filled('school_id')) {
                $participantsQuery->where('school_id', $request->school_id);
            }

            // Filter by subject if provided
            if ($request->filled('subject_id')) {
                $participantsQuery->whereHas('exam.sessions', function ($query) use ($request) {
                    $query->where('subject_id', $request->subject_id);
                });
            }

            $participants = $participantsQuery->get();

            $data = $participants->map(function ($participant) {
                $name = '';
                if ($participant->participant_type === 'App\\Models\\Student') {
                    $name = $participant->participant->student_name ?? '';
                } elseif ($participant->participant_type === 'App\\Models\\Employee') {
                    $name = $participant->participant->employee_name ?? '';
                }

                $subjects = $participant->exam->sessions->pluck('subject.subject_name')->unique()->implode(', ');

                return [
                    'participant_name' => $name,
                    'school_name' => $participant->school->school_name,
                    'subjects' => $subjects,
                    'exam_name' => $participant->exam->exam_name,
                ];
            });

            return DataTables::collection($data)
                ->addIndexColumn()
                ->make(true);
        }

        // Get schools for filter
        $schools = $branch->schools()->select('id', 'school_name')->get();

        // Get unique subjects from exam sessions that have participants
        $subjects = \App\Models\Subject::whereHas('sessions.exam.participants.school', function ($query) use ($branch) {
            $query->where('branch_id', $branch->id);
        })->select('id', 'subject_name')->distinct()->get();

        return view('cabdin.exam-participants.index', [
            'title' => 'Data Peserta Ujian',
            'schools' => $schools,
            'subjects' => $subjects,
        ]);
    }
}
