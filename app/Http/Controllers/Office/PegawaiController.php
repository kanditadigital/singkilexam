<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Branch;
use Yajra\DataTables\Facades\DataTables;
use App\Services\KanditaService;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
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
            $data = Employee::all();

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
                                    <i class="fas fa-fw fa-pencil-alt"></i> Edit
                                    </button>';
                    $deleteButton = '<button type="button" class="btn btn-outline-danger btn-sm delete ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-times"></i> Hapus
                                    </button>';

                    return $editButton . $deleteButton;
                })
            ->rawColumns(["action"])
            ->make(true);
        }
        return view('office.pegawai.index',[
            'title' => 'Pegawai',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.pegawai.add',[
            'title' => 'Tambah Pegawai',
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
            'employee_name'     => 'required',
            'email'             => 'required|email|unique:employees',
            'employee_type'     => 'required',
            'employee_phone'    => 'nullable|string',
        ]);

        // Generate one password and reuse for plain + hashed (model will hash)
        $rawPassword = $this->kanditaService->generatePassword();

        Employee::create([
            'branch_id'         => $request->branch_id,
            'school_id'         => $request->school_id,
            'employee_name'     => $request->employee_name,
            'email'             => $request->email,
            'employee_phone'    => $request->employee_phone,
            'username'          => $request->email, // set username from email
            'password'          => $rawPassword,
            'pass_text'         => $rawPassword,
            'employee_type'     => $request->employee_type,
        ]);

        toast('Pegawai berhasil ditambahkan', 'success');
        return redirect()->route('pegawai.index');
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
        return view('office.pegawai.edit',[
            'title' => 'Edit Pegawai',
            'pegawai' => Employee::find($id),
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
            'employee_name'     => 'required',
            'email'             => 'required|email|unique:employees,email,' . $id,
            'employee_type'     => 'required',
            'employee_phone'    => 'nullable|string',
        ]);

        Employee::where('id', $id)->update([
            'branch_id'         => $request->branch_id,
            'school_id'         => $request->school_id,
            'employee_name'     => $request->employee_name,
            'email'             => $request->email,
            'employee_phone'    => $request->employee_phone,
            'employee_type'     => $request->employee_type,
        ]);

        toast('Pegawai berhasil diubah', 'success');
        return redirect()->route('pegawai.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Employee::where('id', $id)->delete();
        toast('Pegawai berhasil dihapus', 'success');
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
