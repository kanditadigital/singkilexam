<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KanditaService;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Student;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    protected $kanditaService;

    public function __construct(KanditaService $kanditaService)
    {
        $this->kanditaService = $kanditaService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Student::all();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn("school_name", function ($row) {
                    return $row->school->school_name;
                })
                ->addColumn("branch_name", function ($row) {
                    return $row->branch->branch_name;
                })
                ->addColumn("action", function ($row) {
                    $editButton = '<button type="button" class="btn btn-outline-primary btn-sm edit ml-2" data-id="' . $row->id . '">
                        <i class="fas fa-pencil-alt"></i> Edit
                    </button>';
                    $deleteButton = '<button type="button" class="btn btn-outline-danger btn-sm delete ml-2" data-id="' . $row->id . '">
                        <i class="fas fa-trash"></i> Hapus
                    </button>';
                    return $editButton . $deleteButton;
                })
                ->rawColumns(["action"])
                ->make(true);
        }
        return view('office.siswa.index',[
            'title' => 'Siswa',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.siswa.add',[
            'title' => 'Tambah Siswa',
            'cabdin' => Branch::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateStudent($request);

        $photoPath = $this->storeStudentPhoto($request);

        $rawPassword = $this->kanditaService->generatePassword();

        Student::create($this->studentPayload($validated, $photoPath, $rawPassword));

        toast('Siswa berhasil ditambahkan', 'success');
        return redirect()->route('siswa.index');
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
        return view('office.siswa.edit',[
            'title' => 'Edit Siswa',
            'siswa' => Student::find($id),
            'cabdin' => Branch::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $validated = $this->validateStudent($request, $student->id);

        $photoPath = $this->storeStudentPhoto($request, $student);

        $student->update($this->studentPayload($validated, $photoPath));

        toast('Siswa berhasil diubah', 'success');
        return redirect()->route('siswa.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Student::where('id', $id)->delete();
        toast('Siswa berhasil dihapus', 'success');
        return response()->json(['success' => true]);
    }

    /**
     * Get School By Branch for dropdown
     */
    public function getByBranch($branchId)
    {
        $schools = $this->kanditaService->getSchoolsByBranch($branchId);
        return response()->json($schools);
    }

    private function validateStudent(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'branch_id'         => 'required|exists:branches,id',
            'school_id'         => 'required|exists:schools,id',
            'student_name'      => 'required|string|max:255',
            'student_nisn'      => 'required|string|max:30|unique:students,student_nisn' . ($id ? ',' . $id : ''),
            'student_gender'    => 'required|in:Laki-laki,Perempuan',
            'student_photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }

    private function storeStudentPhoto(Request $request, ?Student $student = null): ?string
    {
        if (!$request->hasFile('student_photo')) {
            return $student?->student_photo;
        }

        $photo = $request->file('student_photo');
        $path = $photo->store('student_photos', 'public');

        if ($student && $student->student_photo && Storage::disk('public')->exists($student->student_photo)) {
            Storage::disk('public')->delete($student->student_photo);
        }

        return $path;
    }

    private function studentPayload(array $data, ?string $photoPath = null, ?string $rawPassword = null): array
    {
        $payload = [
            'branch_id'      => $data['branch_id'],
            'school_id'      => $data['school_id'],
            'student_name'   => $data['student_name'],
            'student_nisn'   => $data['student_nisn'],
            'username'       => $data['student_nisn'],
            'student_gender' => $data['student_gender'],
        ];

        if ($photoPath !== null) {
            $payload['student_photo'] = $photoPath;
        } elseif ($rawPassword !== null) {
            $payload['student_photo'] = 'user.png';
        }

        if ($rawPassword !== null) {
            $payload['password'] = Hash::make($rawPassword);
            $payload['pass_text'] = $rawPassword;
        }

        return $payload;
    }
}
