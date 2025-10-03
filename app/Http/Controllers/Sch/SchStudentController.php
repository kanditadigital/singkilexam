<?php

namespace App\Http\Controllers\Sch;

use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
use App\Models\School;
use App\Models\Student;
use App\Services\KanditaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SchStudentController extends Controller
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
        $schid = Auth::guard('schools')->user()->id;
        if ($request->ajax()) {
            $data = Student::where('school_id', $schid);

            return DataTables::of($data)
                ->addIndexColumn()
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
        return view('school.siswa.index',[
            'title'     => 'Data Siswa'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school.siswa.add',[
            'title'     => 'Tambah Data Siswa'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_name'      => 'required',
            'student_nisn'      => 'required|unique:students',
            'student_gender'    => 'required',
            'student_photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('student_photo')) {
            $photoPath = $request->file('student_photo')->store('student_photos', 'public');
        }else{
            $photoPath = 'user.png';
        }

        // Generate password once
        $rawPassword = $this->kanditaService->generatePassword();
        $idsch = Auth::guard('schools')->user()->id;
        $datasch = School::where('id', $idsch)->first();
        Student::create([
            'branch_id'         => $datasch->branch_id,
            'school_id'         => $idsch,
            'student_name'      => $request->student_name,
            'student_nisn'      => $request->student_nisn,
            'username'          => $request->student_nisn,
            'password'          => Hash::make($rawPassword),
            'student_gender'    => $request->student_gender,
            'student_photo'     => $photoPath,
        ]);

        toast('Siswa berhasil ditambahkan', 'success');
        return redirect()->route('sch.student.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('school.siswa.edit',[
            'title'     => 'Edit Data Siswa',
            'std'       => Student::findOrfail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'student_name'      => 'required',
            'student_nisn'      => 'required',
            'student_gender'    => 'required',
            'student_photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $idsch = Auth::guard('schools')->user()->id;
        $datasch = School::where('id', $idsch)->first();

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('student_photo')) {
            $photoPath = $request->file('student_photo')->store('student_photos', 'public');
        }else{
            $photoPath = $datasch->student_photo;
        }

        // Generate password once
        $rawPassword = $this->kanditaService->generatePassword();
        $stddata = Student::findOrfail($id);
        $stddata->update([
            'branch_id'         => $datasch->branch_id,
            'school_id'         => $idsch,
            'student_name'      => $request->student_name,
            'student_nisn'      => $request->student_nisn,
            'username'          => $request->student_nisn,
            'password'          => Hash::make($rawPassword),
            'student_gender'    => $request->student_gender,
            'student_photo'     => $photoPath,
        ]);

        toast('Siswa berhasil ditambahkan', 'success');
        return redirect()->route('sch.student.index');
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $school = Auth::guard('schools')->user();

        Excel::import(new StudentImport($school->id, $school->branch_id), $request->file('file'));

        toast('Data siswa berhasil diimpor', 'success');
        return redirect()->route('sch.student.index');
    }
}
