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
            $schoolsQuery = $branch->schools()->with(['students', 'examParticipants.exam.sessions.subject']);

            // Filter by school if provided
            if ($request->filled('school_id')) {
                $schoolsQuery->where('id', $request->school_id);
            }

            $schools = $schoolsQuery->get();

            $data = $schools->map(function ($school) use ($request) {
                $examParticipants = $school->examParticipants;

                // Filter by subject if provided
                if ($request->filled('subject_id')) {
                    $examParticipants = $examParticipants->filter(function ($participant) use ($request) {
                        return $participant->exam->sessions->contains('subject_id', $request->subject_id);
                    });
                }

                $subjects = $examParticipants->flatMap(function ($participant) {
                    return $participant->exam->sessions->pluck('subject.subject_name');
                })->unique()->implode(', ');

                return [
                    'school_name' => $school->school_name,
                    'students_count' => $school->students()->count(),
                    'participants_count' => $examParticipants->count(),
                    'subjects' => $subjects,
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
