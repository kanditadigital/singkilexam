<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;
use App\Models\Branch;
use App\Services\EmployeeService;
use Yajra\DataTables\Facades\DataTables;
use App\Services\KanditaService;

class PegawaiController extends Controller
{
    protected $kanditaService;
    protected $employeeService;

    public function __construct(KanditaService $kanditaService, EmployeeService $employeeService)
    {
        $this->kanditaService = $kanditaService;
        $this->employeeService = $employeeService;
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
        $validated = $this->validateEmployee($request);
        $rawPassword = $this->kanditaService->generatePassword();
        Employee::create($this->employeePayload($validated, $rawPassword));

        toast('Pegawai berhasil ditambahkan', 'success');
        return redirect()->route('disdik.pegawai.index');
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
        $employee = Employee::findOrFail($id);

        $validated = $this->validateEmployee($request, $employee->id);

        $employee->update($this->employeePayload($validated));

        toast('Pegawai berhasil diubah', 'success');
        return redirect()->route('disdik.pegawai.index');
    }

    private function validateEmployee(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'branch_id'         => 'required|exists:branches,id',
            'school_id'         => 'required|exists:schools,id',
            'employee_name'     => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:employees,email' . ($id ? ',' . $id : ''),
            'employee_type'     => 'required|string|max:100',
            'employee_phone'    => 'nullable|string|max:50',
        ]);
    }

    private function employeePayload(array $data, ?string $rawPassword = null): array
    {
        $payload = [
            'branch_id'      => $data['branch_id'],
            'school_id'      => $data['school_id'],
            'employee_name'  => $data['employee_name'],
            'email'          => $data['email'],
            'employee_phone' => $data['employee_phone'] ?? null,
            'employee_type'  => $data['employee_type'],
            'username'       => $data['email'],
        ];

        if ($rawPassword !== null) {
            $payload['password'] = Hash::make($rawPassword);
        }

        return $payload;
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
