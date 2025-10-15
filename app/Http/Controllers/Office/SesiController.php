<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamSession;
use App\Models\Exam;
use App\Models\Subject;
use Yajra\DataTables\Facades\DataTables;

class SesiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ExamSession::all()->load('exam', 'subject');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn("exam_name", function ($row) {
                    return $row->exam->exam_name;
                })
                ->addColumn("subject_name", function ($row) {
                    return $row->subject->subject_name;
                })
                ->addColumn("action", function ($row) {
                    $editButton = '<button type="button" class="btn btn-outline-primary btn-sm edit ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-pencil-alt"></i> Edit
                                    </button>';
                    $deleteButton = '<button type="button" class="btn btn-outline-danger btn-sm delete ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-times"></i> Hapus
                                    </button>';

                    return $editButton . $deleteButton;
                })
            ->rawColumns(["action", "subject_name", "exam_name"])
            ->make(true);
        }
        return view('office.sesi.index',[
            'title' => 'Data Sesi Ujian',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.sesi.add',[
            'title' => 'Tambah Sesi Ujian',
            'exams' => Exam::where('exam_status', 'Active')->get(),
            'subjects' => Subject::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_id'           => 'required',
            'session_number'    => 'required',
            'subjects'          => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.question_count' => 'required|integer|min:1',
            'subjects.*.duration' => 'required|integer|min:1',
            'break_duration'    => 'nullable|integer|min:0',
            'session_start_time'=> 'required',
            'session_end_time'  => 'required',
            'random_question'   => 'required',
            'random_answer'     => 'required',
            'show_result'       => 'required',
            'show_score'        => 'required',
            'session_status'    => 'required',
        ]);

        // Check for duplicate subjects
        $subjectIds = collect($request->subjects)->pluck('subject_id')->unique();
        if ($subjectIds->count() !== collect($request->subjects)->count()) {
            return back()->withErrors(['subjects' => 'Mata pelajaran tidak boleh duplikat dalam satu sesi.']);
        }

        $sessionSubjects = collect($request->subjects)->map(function ($subject) {
            return [
                'subject_id' => $subject['subject_id'],
                'question_count' => $subject['question_count'],
                'duration' => $subject['duration'],
            ];
        })->toArray();

        ExamSession::create([
            'exam_id'           => $request->exam_id,
            'session_number'    => $request->session_number,
            'subject_id'        => $sessionSubjects[0]['subject_id'], // Keep for backward compatibility
            'session_start_time'=> $request->session_start_time,
            'session_end_time'  => $request->session_end_time,
            'session_duration'  => collect($sessionSubjects)->sum('duration'), // Total duration from all subjects
            'break_duration'    => $request->break_duration ?? 0,
            'random_question'   => $request->random_question,
            'random_answer'     => $request->random_answer,
            'show_result'       => $request->show_result,
            'show_score'        => $request->show_score,
            'session_status'    => $request->session_status,
            'session_subjects'  => $sessionSubjects,
        ]);

        toast('Sesi Ujian berhasil ditambahkan', 'success');
        return redirect()->route('disdik.sesi-ujian.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('office.sesi.edit',[
            'title' => 'Edit Sesi Ujian',
            'sesi' => ExamSession::find($id)->load('exam', 'subject'),
            'exam' => Exam::where('exam_status', 'Active')->get(),
            'subjects' => Subject::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'exam_id'           => 'required',
            'session_number'    => 'required',
            'subjects'          => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.question_count' => 'required|integer|min:1',
            'subjects.*.duration' => 'required|integer|min:1',
            'break_duration'    => 'nullable|integer|min:0',
            'session_start_time'=> 'required',
            'session_end_time'  => 'required',
            'random_question'   => 'required',
            'random_answer'     => 'required',
            'show_result'       => 'required',
            'show_score'        => 'required',
            'session_status'    => 'required',
        ]);

        // Check for duplicate subjects
        $subjectIds = collect($request->subjects)->pluck('subject_id')->unique();
        if ($subjectIds->count() !== collect($request->subjects)->count()) {
            return back()->withErrors(['subjects' => 'Mata pelajaran tidak boleh duplikat dalam satu sesi.']);
        }

        $sessionSubjects = collect($request->subjects)->map(function ($subject) {
            return [
                'subject_id' => $subject['subject_id'],
                'question_count' => $subject['question_count'],
                'duration' => $subject['duration'],
            ];
        })->toArray();

        ExamSession::where('id', $id)->update([
            'exam_id'           => $request->exam_id,
            'session_number'    => $request->session_number,
            'subject_id'        => $sessionSubjects[0]['subject_id'], // Keep for backward compatibility
            'session_start_time'=> $request->session_start_time,
            'session_end_time'  => $request->session_end_time,
            'session_duration'  => collect($sessionSubjects)->sum('duration'), // Total duration from all subjects
            'break_duration'    => $request->break_duration ?? 0,
            'random_question'   => $request->random_question,
            'random_answer'     => $request->random_answer,
            'show_result'       => $request->show_result,
            'show_score'        => $request->show_score,
            'session_status'    => $request->session_status,
            'session_subjects'  => $sessionSubjects,
        ]);

        toast('Sesi Ujian berhasil diubah', 'success');
        return redirect()->route('disdik.sesi-ujian.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        ExamSession::where('id', $id)->delete();
        toast('Sesi Ujian berhasil dihapus', 'success');
        return response()->json(['success' => true]);
    }

    /**
     * Get sessions by exam ID for AJAX requests
     */
    public function getByExam($examId)
    {
        $sessions = ExamSession::where('exam_id', $examId)
            ->with('subject')
            ->get();

        return response()->json($sessions);
    }
}
