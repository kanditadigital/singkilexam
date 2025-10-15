<?php

namespace App\Http\Controllers\Sch;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ParticipantCardsExport;

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

        $query = ExamParticipant::with(['participant', 'examSession.subject'])
            ->where('school_id', $schoolId)
            ->when($examId, fn ($q) => $q->where('exam_id', $examId))
            ->when($filterType, function ($q) use ($filterType) {
                $q->where('participant_type', $this->participantClass($filterType));
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function (ExamParticipant $participant) {
                return '<div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input registered-select" id="registered-checkbox-' . $participant->id . '" value="' . $participant->id . '">
                    <label class="custom-control-label" for="registered-checkbox-' . $participant->id . '"></label>
                </div>';
            })
            ->addColumn('type_label', fn (ExamParticipant $participant) => $this->typeLabelFromClass($participant->participant_type))
            ->addColumn('name', fn (ExamParticipant $participant) => $this->participantName($participant))
            ->addColumn('identifier', fn (ExamParticipant $participant) => $this->participantIdentifier($participant))
            ->addColumn('meta', fn (ExamParticipant $participant) => $this->participantMeta($participant))
            ->addColumn('session', function (ExamParticipant $participant) {
                return $participant->examSession ? $participant->examSession->subject->subject_name : '-';
            })
            ->addColumn('action', function (ExamParticipant $participant) {
                return '<button type="button" class="btn btn-outline-danger btn-sm remove-participant" data-id="' . $participant->id . '"><i class="fas fa-trash"></i> Hapus</button>';
            })
            ->rawColumns(['checkbox', 'action'])
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

    public function bulkDestroy(Request $request)
    {
        $schoolId = Auth::guard('schools')->id();
        $data = $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'integer',
        ]);

        $ids = collect($data['participant_ids'])->unique()->values();
        if ($ids->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada peserta dipilih.',
            ]);
        }

        $participants = ExamParticipant::whereIn('id', $ids)
            ->where('school_id', $schoolId)
            ->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Peserta tidak ditemukan.',
            ], 404);
        }

        DB::transaction(function () use ($participants, $schoolId) {
            foreach ($participants as $participant) {
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
            }

            ExamParticipant::whereIn('id', $participants->pluck('id'))->delete();
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

    private function generateRandomUsername(): string
    {
        return Str::random(6, '0123456789abcdefghijklmnopqrstuvwxyz');
    }

    public function participantCards(Request $request)
    {
        $school = Auth::guard('schools')->user();
        $exams = Exam::orderByDesc('created_at')->get();

        return view('school.participant-cards.index', [
            'title' => 'Kartu Peserta Ujian',
            'exams' => $exams,
            'selectedExamId' => $request->integer('exam_id'),
            'selectedType' => $request->input('type'),
            'school' => $school,
        ]);
    }

    public function previewCards(Request $request)
    {
        $schoolId = Auth::guard('schools')->id();
        $examId = $request->integer('exam_id');
        $type = $request->input('type');

        if (!$examId) {
            return response()->json(['error' => 'Pilih ujian terlebih dahulu.'], 400);
        }

        $query = ExamParticipant::with(['participant', 'exam'])
            ->where('exam_id', $examId)
            ->where('school_id', $schoolId);

        if ($type && in_array($type, ['student', 'employee'])) {
            $participantClass = $this->participantClass($type);
            $query->where('participant_type', $participantClass);
        }

        $participants = $query->get()
            ->map(function ($participant) {
                $user = $participant->participant;
                if ($participant->participant_type === Employee::class) {
                    $username = $user->username ?? $this->generateRandomUsername();
                    $identifier = $username;
                    $password = $username . '*';
                } else {
                    $identifier = $this->participantIdentifier($participant);
                    $password = $user->student_nisn . '*';
                }
                return [
                    'name' => $this->participantName($participant),
                    'identifier' => $identifier,
                    'type' => $this->typeLabelFromClass($participant->participant_type),
                    'exam_name' => $participant->exam->exam_name,
                    'exam_code' => $participant->exam->exam_code,
                    'school_name' => Auth::guard('schools')->user()->school_name,
                    'photo' => $user ? ($participant->participant_type === \App\Models\Employee::class ? $user->employee_photo : $user->student_photo) : null,
                    'password' => $password,
                    'meta' => $this->participantMeta($participant),
                ];
            });

        $exam = Exam::find($examId);

        $pdf = Pdf::loadView('school.participant-cards.cards-pdf', [
            'participants'  => $participants,
            'exam'          => $exam,
            'school'        => Auth::guard('schools')->user(),
        ])->setPaper('a4', 'landscape');

        // Get PDF content as string and base64 encode for preview
        $pdfContent = $pdf->output();
        $base64Pdf = base64_encode($pdfContent);
        $dataUrl = 'data:application/pdf;base64,' . $base64Pdf;

        return response()->json([
            'pdf_url' => $dataUrl,
        ]);
    }

    public function downloadCards(Request $request)
    {
        $schoolId = Auth::guard('schools')->id();
        $examId = $request->integer('exam_id');
        $type = $request->input('type');

        if (!$examId) {
            return response()->json(['error' => 'Pilih ujian terlebih dahulu.'], 400);
        }

        $query = ExamParticipant::with(['participant', 'exam'])
            ->where('exam_id', $examId)
            ->where('school_id', $schoolId);

        if ($type && in_array($type, ['student', 'employee'])) {
            $participantClass = $this->participantClass($type);
            $query->where('participant_type', $participantClass);
        }

        $participants = $query->get()
            ->map(function ($participant) {
                $user = $participant->participant;
                if ($participant->participant_type === Employee::class) {
                    $username = $user->username ?? $this->generateRandomUsername();
                    $identifier = $username;
                    $password = $username . '*';
                } else {
                    $identifier = $this->participantIdentifier($participant);
                    $password = $user->student_nisn . '*';
                }
                return [
                    'name' => $this->participantName($participant),
                    'identifier' => $identifier,
                    'type' => $this->typeLabelFromClass($participant->participant_type),
                    'exam_name' => $participant->exam->exam_name,
                    'exam_code' => $participant->exam->exam_code,
                    'school_name' => Auth::guard('schools')->user()->school_name,
                    'photo' => $user ? ($participant->participant_type === \App\Models\Employee::class ? $user->employee_photo : $user->student_photo) : null,
                    'password' => $password,
                    'meta' => $this->participantMeta($participant),
                ];
            });

        $exam = Exam::find($examId);

        $pdf = Pdf::loadView('school.participant-cards.cards-pdf', [
            'participants' => $participants,
            'exam' => $exam,
            'school' => Auth::guard('schools')->user(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $typeSuffix = $type ? '-' . $type : '';
        $filename = 'kartu-peserta-' . Str::slug($exam->exam_name) . $typeSuffix . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
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
                $user = $participant->participant;
                if ($participant->participant_type === Employee::class) {
                    $username = $user->username ?? $this->generateRandomUsername();
                    $identifier = $username;
                    $password = $username . '*';
                } else {
                    $identifier = $this->participantIdentifier($participant);
                    $password = $user->student_nisn . '*';
                }
                return [
                    'name' => $this->participantName($participant),
                    'identifier' => $identifier,
                    'type' => $this->typeLabelFromClass($participant->participant_type),
                    'exam_name' => $participant->exam->exam_name,
                    'exam_code' => $participant->exam->exam_code,
                    'school_name' => Auth::guard('schools')->user()->school_name,
                    'photo' => $user ? ($participant->participant_type === \App\Models\Employee::class ? $user->employee_photo : $user->student_photo) : null,
                    'password' => $password,
                    'meta' => $this->participantMeta($participant),
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

    public function examMonitoring(Request $request)
    {
        $school = Auth::guard('schools')->user();

        if ($request->ajax()) {
            $query = ExamAttempt::with(['participant', 'session.subject'])
                ->where(function ($q) use ($school) {
                    $q->where(function ($subQ) use ($school) {
                        $subQ->where('participant_type', Student::class)
                             ->whereHas('participant', function ($pQ) use ($school) {
                                 $pQ->where('school_id', $school->id);
                             });
                    })->orWhere(function ($subQ) use ($school) {
                        $subQ->where('participant_type', Employee::class)
                             ->whereHas('participant', function ($pQ) use ($school) {
                                 $pQ->where('school_id', $school->id);
                             });
                    });
                })
                ->whereNotNull('started_at');

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'ongoing') {
                    $query->whereNull('submitted_at');
                } elseif ($request->status === 'submitted') {
                    $query->whereNotNull('submitted_at');
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('participant_name', function ($attempt) {
                    return $attempt->participant_type === Employee::class
                        ? ($attempt->participant->employee_name ?? '-')
                        : ($attempt->participant->student_name ?? '-');
                })
                ->addColumn('subject_name', function ($attempt) {
                    return optional($attempt->session)->subject_display_name ?: '-';
                })
                ->addColumn('started_at_formatted', function ($attempt) {
                    return $attempt->started_at ? $attempt->started_at->format('d/m/Y H:i') : '-';
                })
                ->addColumn('duration', function ($attempt) {
                    if ($attempt->submitted_at) {
                        $duration = $attempt->submitted_at->diff($attempt->started_at);
                        return $duration->format('%H:%I:%S');
                    } else {
                        $duration = now()->diff($attempt->started_at);
                        return $duration->format('%H:%I:%S');
                    }
                })
                ->addColumn('submitted_at_formatted', function ($attempt) {
                    return $attempt->submitted_at ? $attempt->submitted_at->format('d/m/Y H:i') : '-';
                })
                ->make(true);
        }

        return view('school.exam-monitoring.index', [
            'title' => 'Monitoring Ujian',
            'school' => $school,
        ]);
    }
}
