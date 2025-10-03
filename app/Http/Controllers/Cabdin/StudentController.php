<?php

namespace App\Http\Controllers\Cabdin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\KanditaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function __construct(private KanditaService $service)
    {
    }

    /**
     * Tampilkan daftar siswa (DataTables / view).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Student::with('school')
                ->where('branch_id', $this->branch()->id)
                ->when($request->school_id, function ($query) use ($request) {
                    $query->where('school_id', $request->school_id);
                })
                ->orderBy('student_name');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('school_name', fn($row) => $row->school?->school_name ?? '-')
                ->addColumn('gender', fn($row) =>
                    $row->student_gender === 'Laki-laki'
                        ? '<span class="badge badge-info">Laki-laki</span>'
                        : '<span class="badge badge-pink">Perempuan</span>'
                )
                ->addColumn('action', fn($row) => $this->actionButtons($row->id))
                ->rawColumns(['gender','action'])
                ->make(true);
        }

        return view('cabdin.students.index', [
            'title'   => 'Data Siswa',
            'schools' => $this->branch()->schools()->orderBy('school_name')->get(),
        ]);
    }

    /**
     * Form tambah siswa.
     */
    public function create()
    {
        return view('cabdin.students.create', [
            'title'   => 'Tambah Siswa',
            'schools' => $this->branch()->schools()->where('is_active', true)->orderBy('school_name')->get(),
        ]);
    }

    /**
     * Simpan siswa baru.
     */
    public function store(Request $request)
    {
        $branch = $this->branch();
        $data = $this->validateStudent($request, $branch->id, null, true);

        $photoPath = $this->storePhoto($request);
        $rawPassword = $this->service->generatePassword();

        $student = Student::create($this->studentPayload($branch->id, $data, $photoPath, $rawPassword));

        session()->flash('student_credentials', [
            'name'     => $student->student_name,
            'username' => $student->username,
            'password' => $rawPassword,
        ]);

        toast('Siswa berhasil ditambahkan', 'success');
        return redirect()->route('cabdin.students.index');
    }

    /**
     * Form edit siswa.
     */
    public function edit(Student $student)
    {
        $this->ensureOwnedByBranch($student);

        return view('cabdin.students.edit', [
            'title'   => 'Edit Siswa',
            'student' => $student,
            'schools' => $this->branch()->schools()->orderBy('school_name')->get(),
        ]);
    }

    /**
     * Update siswa.
     */
    public function update(Request $request, Student $student)
    {
        $this->ensureOwnedByBranch($student);
        $branch = $this->branch();

        $data = $this->validateStudent($request, $branch->id, $student->id, false);
        $photoPath = $this->storePhoto($request, $student);

        $student->update($this->studentPayload($branch->id, $data, $photoPath));

        toast('Data siswa diperbarui', 'success');
        return redirect()->route('cabdin.students.index');
    }

    /**
     * Hapus siswa (AJAX).
     */
    public function destroy(Student $student)
    {
        $this->ensureOwnedByBranch($student);

        if ($student->student_photo && Storage::disk('public')->exists($student->student_photo)) {
            Storage::disk('public')->delete($student->student_photo);
        }

        $student->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Siswa berhasil dihapus'
        ]);
    }

    /**
     * Reset password siswa.
     */
    public function resetPassword(Student $student, Request $request)
    {
        $this->ensureOwnedByBranch($student);

        $rawPassword = $this->service->generatePassword();
        $student->update([
            'password'  => Hash::make($rawPassword),
            'pass_text' => $rawPassword,
        ]);

        return response()->json([
            'ok'      => true,
            'student' => [
                'name'   => $student->student_name,
                'nisn'   => $student->student_nisn,
                'school' => $student->school?->school_name,
            ],
            'password'=> $rawPassword,
        ]);
    }

    /* =====================
     * Helpers
     * ===================== */

    protected function validateStudent(Request $request, int $branchId, ?int $ignoreId = null, bool $requireActive = true): array
    {
        $schoolRule = Rule::exists('schools', 'id')->where(function ($query) use ($branchId, $requireActive) {
            $query->where('branch_id', $branchId);
            if ($requireActive) {
                $query->where('is_active', true);
            }
        });

        return $request->validate([
            'school_id'      => ['required', $schoolRule],
            'student_name'   => ['required', 'string', 'max:255'],
            'student_nisn'   => ['required', 'string', 'max:30', Rule::unique('students', 'student_nisn')->ignore($ignoreId)],
            'student_gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'student_photo'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);
    }

    protected function storePhoto(Request $request, ?Student $student = null): ?string
    {
        if (! $request->hasFile('student_photo')) {
            return $student?->student_photo;
        }

        $path = $request->file('student_photo')->store('student_photos', 'public');

        if ($student && $student->student_photo && Storage::disk('public')->exists($student->student_photo)) {
            Storage::disk('public')->delete($student->student_photo);
        }

        return $path;
    }

    protected function studentPayload(int $branchId, array $data, ?string $photoPath = null, ?string $rawPassword = null): array
    {
        $payload = [
            'branch_id'      => $branchId,
            'school_id'      => $data['school_id'],
            'student_name'   => $data['student_name'],
            'student_nisn'   => $data['student_nisn'],
            'student_gender' => $data['student_gender'],
            'username'       => $data['student_nisn'],
        ];

        if ($photoPath !== null) {
            $payload['student_photo'] = $photoPath;
        }

        if ($rawPassword !== null) {
            $payload['password']  = Hash::make($rawPassword);
            $payload['pass_text'] = $rawPassword;
        }

        return $payload;
    }

    protected function ensureOwnedByBranch(Student $student): void
    {
        if ($student->branch_id !== $this->branch()->id) {
            abort(403);
        }
    }

    protected function branch()
    {
        return Auth::guard('branches')->user();
    }

    protected function actionButtons(int $id): string
    {
        $edit = '<button type="button" class="btn btn-outline-primary btn-sm edit" data-id="'.$id.'" title="Edit">
                    <i class="fas fa-pencil-alt"></i>
                 </button>';

        $delete = '<button type="button" class="btn btn-outline-danger btn-sm delete ml-1" data-id="'.$id.'" title="Hapus">
                      <i class="fas fa-trash"></i>
                   </button>';

        $reset = '<button type="button" class="btn btn-outline-warning btn-sm reset ml-1" data-id="'.$id.'" title="Reset Password">
                     <i class="fas fa-key"></i>
                  </button>';

        return $edit . $delete . $reset;
    }
}
