<?php

namespace App\Http\Controllers\Sch;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamParticipantLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Yajra\DataTables\Facades\DataTables;

class SchExamParticipantController extends Controller
{
    public function index(Request $request)
    {
        $school = Auth::guard('schools')->user();
        $exams = Exam::orderByDesc('created_at')->get();

        return view('school.exam-participants.index', [
            'title' => 'Peserta Ujian',
            'exams' => $exams,
            'selectedExamId' => $request->integer('exam_id'),
            'school' => $school,
        ]);
    }

    public function students(Request $request)
    {
        $schoolId = Auth::guard('schools')->id();
        $examId = $request->integer('exam_id');

        $query = Student::with('school')
            ->where('school_id', $schoolId);

        if ($gender = $request->input('gender')) {
            $query->where('student_gender', $gender);
        }

        $existing = collect();
        if ($examId) {
            $existing = ExamParticipant::where('exam_id', $examId)
                ->where('school_id', $schoolId)
                ->pluck('student_id');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('is_registered', function (Student $student) use ($existing) {
                return $existing->contains($student->id);
            })
            ->addColumn('checkbox', function (Student $student) use ($existing) {
                if ($existing->contains($student->id)) {
                    return '<span class="badge badge-success">Terdaftar</span>';
                }

                return '<div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input student-select" id="student-checkbox-' . $student->id . '" value="' . $student->id . '">
                    <label class="custom-control-label" for="student-checkbox-' . $student->id . '"></label>
                </div>';
            })
            ->rawColumns(['checkbox'])
            ->toJson();
    }

    public function registered(Request $request)
    {
        $schoolId = Auth::guard('schools')->id();
        $examId = $request->integer('exam_id');

        $query = ExamParticipant::with(['student'])
            ->where('school_id', $schoolId)
            ->when($examId, fn($q) => $q->where('exam_id', $examId));

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('student_name', fn(ExamParticipant $participant) => $participant->student->student_name ?? '-')
            ->addColumn('student_gender', fn(ExamParticipant $participant) => $participant->student->student_gender ?? '-')
            ->addColumn('action', function (ExamParticipant $participant) {
                return '<button type="button" class="btn btn-outline-danger btn-sm remove-participant" data-id="' . $participant->id . '"><i class="fas fa-trash"></i> Hapus</button>';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $school = Auth::guard('schools')->user();
        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $studentIds = collect($data['student_ids'])->unique()->values();
        if ($studentIds->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada siswa dipilih.',
            ]);
        }

        DB::transaction(function () use ($data, $studentIds, $school) {
            /** @var Collection<int, int> $validIds */
            $validIds = Student::whereIn('id', $studentIds)
                ->where('school_id', $school->id)
                ->pluck('id');

            if ($validIds->isEmpty()) {
                throw new HttpException(422, 'Tidak ada siswa yang valid untuk didaftarkan.');
            }

            $existing = ExamParticipant::where('exam_id', $data['exam_id'])
                ->whereIn('student_id', $validIds)
                ->pluck('student_id');

            $newIds = $validIds->diff($existing);

            $now = now();
            $newIds->each(function ($studentId) use ($data, $school, $now) {
                $participant = ExamParticipant::create([
                    'exam_id' => $data['exam_id'],
                    'school_id' => $school->id,
                    'student_id' => $studentId,
                    'created_by' => $school->id,
                ]);

                ExamParticipantLog::create([
                    'exam_id' => $participant->exam_id,
                    'school_id' => $participant->school_id,
                    'student_id' => $participant->student_id,
                    'performed_by' => $school->id,
                    'action' => 'added',
                    'meta' => [
                        'participant_id' => $participant->id,
                        'timestamp' => $now->toISOString(),
                    ],
                ]);
            });
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Peserta ujian berhasil disimpan.',
        ]);
    }

    public function destroy(ExamParticipant $participant)
    {
        $schoolId = Auth::guard('schools')->id();
        if ($participant->school_id !== $schoolId) {
            abort(403);
        }

        DB::transaction(function () use ($participant, $schoolId) {
            ExamParticipantLog::create([
                'exam_id' => $participant->exam_id,
                'school_id' => $participant->school_id,
                'student_id' => $participant->student_id,
                'performed_by' => $schoolId,
                'action' => 'removed',
                'meta' => [
                    'participant_id' => $participant->id,
                    'timestamp' => now()->toISOString(),
                ],
            ]);

            $participant->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Peserta ujian berhasil dihapus.',
        ]);
    }
}
