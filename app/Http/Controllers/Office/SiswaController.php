<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KanditaService;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Student;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'branch_id'         => 'required',
            'school_id'         => 'required',
            'student_name'      => 'required',
            'student_nisn'      => 'required|unique:students',
            'student_gender'    => 'required',
            'student_photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        Student::create([
            'branch_id'         => $request->branch_id,
            'school_id'         => $request->school_id,
            'student_name'      => $request->student_name,
            'student_nisn'      => $request->student_nisn,
            'username'          => $request->student_nisn,
            'password'          => Hash::make($this->kanditaService->generatePassword()),
            'pass_text'         => $this->kanditaService->generatePassword(),
            'student_gender'    => $request->student_gender,
            'student_photo'     => $request->student_photo,
        ]);

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
        $request->validate([
            'branch_id'         => 'required',
            'school_id'         => 'required',
            'student_name'      => 'required',
            'student_nisn'      => 'required|unique:students,student_nisn,' . $id,
            'student_gender'    => 'required',
            'student_photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        Student::where('id', $id)->update([
            'branch_id'         => $request->branch_id,
            'school_id'         => $request->school_id,
            'student_name'      => $request->student_name,
            'username'          => $request->student_nisn,
            'student_nisn'      => $request->student_nisn,
            'student_gender'    => $request->student_gender,
            'student_photo'     => $request->student_photo,
        ]);

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
}
