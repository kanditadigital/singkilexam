<?php

namespace App\Http\Controllers\Sch;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamParticipantLog;
use App\Models\Student;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

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
        $type = $this->resolveType($request->input('type'));
        $participantClass = $this->participantClass($type);

        if ($type === 'employee') {
            $query = Employee::where('school_id', $schoolId);
            if ($employeeType = $request->input('employee_type')) {
                $query->where('employee_type', $employeeType);
            }
        } else {
            $query = Student::where('school_id', $schoolId);
            if ($gender = $request->input('gender')) {
                $query->where('student_gender', $gender);
            }
        }

        $existing = collect();
        if ($examId) {
            $existing = ExamParticipant::where('exam_id', $examId)
                ->where('school_id', $schoolId)
                ->where('participant_type', $participantClass)
                ->pluck('participant_id');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('is_registered', fn ($model) => $existing->contains($model->id))
            ->addColumn('name', function ($model) use ($type) {
                return $type === 'employee' ? $model->employee_name : $model->student_name;
            })
            ->addColumn('identifier', function ($model) use ($type) {
                return $type === 'employee' ? ($model->username ?? $model->email) : $model->student_nisn;
            })
            ->addColumn('meta', function ($model) use ($type) {
                return $type === 'employee' ? ($model->employee_type ?? '-') : ($model->student_gender ?? '-');
            })
            ->addColumn('checkbox', function ($model) use ($existing) {
                if ($existing->contains($model->id)) {
                    return '<span class="badge badge-success">Terdaftar</span>';
                }

                return '<div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input participant-select" id="participant-checkbox-' . $model->id . '" value="' . $model->id . '">
                    <label class="custom-control-label" for="participant-checkbox-' . $model->id . '"></label>
                </div>';
            })
            ->rawColumns(['checkbox'])
            ->toJson();
    }

    public function registered(Request $request)
    {
        $schoolId = Auth::guard('schools')->id();
        $examId = $request->integer('exam_id');
        $filterType = $request->has('type') ? $this->resolveType($request->input('type')) : null;

        $query = ExamParticipant::with(['participant'])
            ->where('school_id', $schoolId)
            ->when($examId, fn ($q) => $q->where('exam_id', $examId))
            ->when($filterType, function ($q) use ($filterType) {
                $q->where('participant_type', $this->participantClass($filterType));
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('type_label', fn (ExamParticipant $participant) => $this->typeLabelFromClass($participant->participant_type))
            ->addColumn('name', fn (ExamParticipant $participant) => $this->participantName($participant))
            ->addColumn('identifier', fn (ExamParticipant $participant) => $this->participantIdentifier($participant))
            ->addColumn('meta', fn (ExamParticipant $participant) => $this->participantMeta($participant))
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
            'participant_type' => 'required|in:student,employee',
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'integer',
        ]);

        $participantClass = $this->participantClass($data['participant_type']);

        $ids = collect($data['participant_ids'])->unique()->values();
        if ($ids->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada peserta dipilih.',
            ]);
        }

        DB::transaction(function () use ($data, $ids, $school, $participantClass) {
            $validIds = $this->validParticipantIds($participantClass, $ids, $school->id);

            if ($validIds->isEmpty()) {
                throw new HttpException(422, 'Tidak ada peserta yang valid untuk didaftarkan.');
            }

            $existing = ExamParticipant::where('exam_id', $data['exam_id'])
                ->where('school_id', $school->id)
                ->where('participant_type', $participantClass)
                ->whereIn('participant_id', $validIds)
                ->pluck('participant_id');

            $newIds = $validIds->diff($existing);

            $now = now();
            $newIds->each(function ($participantId) use ($data, $school, $now, $participantClass) {
                $participant = ExamParticipant::create([
                    'exam_id' => $data['exam_id'],
                    'school_id' => $school->id,
                    'participant_type' => $participantClass,
                    'participant_id' => $participantId,
                    'created_by' => $school->id,
                ]);

                ExamParticipantLog::create([
                    'exam_id' => $participant->exam_id,
                    'school_id' => $participant->school_id,
                    'participant_type' => $participant->participant_type,
                    'participant_id' => $participant->participant_id,
                    'performed_by' => $school->id,
                    'action' => 'added',
                    'meta' => [
                        'participant_record_id' => $participant->id,
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
                'participant_type' => $participant->participant_type,
                'participant_id' => $participant->participant_id,
                'performed_by' => $schoolId,
                'action' => 'removed',
                'meta' => [
                    'participant_record_id' => $participant->id,
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

    private function resolveType(?string $type): string
    {
        $type = $type ? strtolower($type) : 'student';
        return in_array($type, ['student', 'employee'], true) ? $type : 'student';
    }

    private function participantClass(string $type): string
    {
        return $type === 'employee' ? Employee::class : Student::class;
    }

    private function typeLabelFromClass(?string $class): string
    {
        if ($class === Employee::class) {
            return 'Guru/Staff';
        }
        return 'Siswa';
    }

    private function participantName(ExamParticipant $participant): string
    {
        $user = $participant->participant;
        if (!$user) {
            return '-';
        }

        return $participant->participant_type === Employee::class
            ? ($user->employee_name ?? '-')
            : ($user->student_name ?? '-');
    }

    private function participantIdentifier(ExamParticipant $participant): string
    {
        $user = $participant->participant;
        if (!$user) {
            return '-';
        }

        return $participant->participant_type === Employee::class
            ? ($user->username ?? $user->email ?? '-')
            : ($user->student_nisn ?? '-');
    }

    private function participantMeta(ExamParticipant $participant): string
    {
        $user = $participant->participant;
        if (!$user) {
            return '-';
        }

        return $participant->participant_type === Employee::class
            ? ($user->employee_type ?? '-')
            : ($user->student_gender ?? '-');
    }

    private function validParticipantIds(string $class, Collection $ids, int $schoolId): Collection
    {
        if ($class === Employee::class) {
            return Employee::whereIn('id', $ids)
                ->where('school_id', $schoolId)
                ->pluck('id');
        }

        return Student::whereIn('id', $ids)
            ->where('school_id', $schoolId)
            ->pluck('id');
    }

    public function printCards(Exam $exam)
    {
        $schoolId = Auth::guard('schools')->id();
        if (!$exam) {
            abort(404);
        }

        $participants = ExamParticipant::with(['participant', 'exam'])
            ->where('exam_id', $exam->id)
            ->where('school_id', $schoolId)
            ->get()
            ->map(function ($participant) {
                return [
                    'name' => $this->participantName($participant),
                    'identifier' => $this->participantIdentifier($participant),
                    'type' => $this->typeLabelFromClass($participant->participant_type),
                    'exam_name' => $participant->exam->exam_name,
                    'exam_code' => $participant->exam->exam_code,
                    'school_name' => Auth::guard('schools')->user()->school_name,
                ];
            });

        $pdf = Pdf::loadView('school.exam-participants.cards-pdf', [
            'participants' => $participants,
            'exam' => $exam,
            'school' => Auth::guard('schools')->user(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'kartu-peserta-' . Str::slug($exam->exam_name) . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function printMinutes(Exam $exam)
    {
        $schoolId = Auth::guard('schools')->id();
        if (!$exam) {
            abort(404);
        }

        $participants = ExamParticipant::with(['participant'])
            ->where('exam_id', $exam->id)
            ->where('school_id', $schoolId)
            ->get()
            ->map(function ($participant) {
                return [
                    'name' => $this->participantName($participant),
                    'identifier' => $this->participantIdentifier($participant),
                    'type' => $this->typeLabelFromClass($participant->participant_type),
                    'meta' => $this->participantMeta($participant),
                ];
            });

        $pdf = Pdf::loadView('school.exam-participants.minutes-pdf', [
            'participants' => $participants,
            'exam' => $exam,
            'school' => Auth::guard('schools')->user(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'berita-acara-' . Str::slug($exam->exam_name) . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
