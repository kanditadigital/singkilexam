<?php

namespace App\Http\Controllers\Sch;

use App\Http\Controllers\Controller;
use App\Imports\EmployeeImport;
use App\Models\Employee;
use App\Services\KanditaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SchEmployeeController extends Controller
{
    public function __construct(private readonly KanditaService $kanditaService)
    {
    }

    public function index(Request $request)
    {
        $schoolId = Auth::guard('schools')->id();

        if ($request->ajax()) {
            $data = Employee::where('school_id', $schoolId);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function (Employee $row) {
                    $editButton = '<button type="button" class="btn btn-outline-primary btn-sm edit ml-2" data-id="'
                        . $row->id . '\">'
                        . '<i class="fas fa-pencil-alt"></i> Edit'
                        . '</button>';
                    $resetButton = '<button type="button" class="btn btn-warning btn-sm reset-password ml-2" data-id="'
                        . $row->id . '" data-name="' . $row->employee_name . '\">'
                        . '<i class="fas fa-key"></i> Reset'
                        . '</button>';
                    $deleteButton = '<button type="button" class="btn btn-outline-danger btn-sm delete ml-2" data-id="'
                        . $row->id . '\">'
                        . '<i class="fas fa-trash"></i> Hapus'
                        . '</button>';
                    return $editButton . $resetButton . $deleteButton;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('school.pegawai.index', [
            'title' => 'Data Guru & Staff',
        ]);
    }

    /**
     *
     */
    public function create()
    {
        return view('school.pegawai.add', [
            'title' => 'Tambah Guru & Staff',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_name'     => 'required|string|max:255',
            'email'             => 'nullable|email|unique:employees,email',
            'employee_phone'    => 'nullable|string|max:50',
            'employee_type'     => 'required|string|max:100',
        ]);

        $school = Auth::guard('schools')->user();
        $username = $this->generateRandomUsername();
        $rawPassword = $this->kanditaService->generatePassword();

        Employee::create([
            'branch_id'         => $school->branch_id,
            'school_id'         => $school->id,
            'employee_name'     => $request->employee_name,
            'email'             => $request->email,
            'username'          => $username,
            'employee_phone'    => $request->employee_phone,
            'employee_type'     => $request->employee_type,
            'password'          => Hash::make($rawPassword),
        ]);

        toast('Data guru/staff berhasil ditambahkan', 'success');
        return redirect()->route('sch.employee.index');
    }

    private function generateRandomUsername(): string
    {
        return Str::random(6, '0123456789abcdefghijklmnopqrstuvwxyz');
    }

    public function edit(string $id)
    {
        $employee = Employee::where('school_id', Auth::guard('schools')->id())->findOrFail($id);

        return view('school.pegawai.edit', [
            'title' => 'Edit Guru & Staff',
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::where('school_id', Auth::guard('schools')->id())->findOrFail($id);

        $request->validate([
            'employee_name'     => 'required|string|max:255',
            'email'             => 'nullable|email|unique:employees,email,' . $employee->id,
            'employee_phone'    => 'nullable|string|max:50',
            'employee_type'     => 'required|string|max:100',
            'reset_password'    => 'nullable|boolean',
        ]);

        $payload = [
            'employee_name'     => $request->employee_name,
            'email'             => $request->email,
            'employee_phone'    => $request->employee_phone,
            'employee_type'     => $request->employee_type,
        ];

        if ($request->boolean('reset_password')) {
            $newPassword = $this->kanditaService->generatePassword();
            $payload['password'] = Hash::make($newPassword);
        }

        $employee->update($payload);

        toast('Data guru/staff berhasil diperbarui', 'success');
        return redirect()->route('sch.employee.index');
    }

    public function destroy(string $id)
    {
        $employee = Employee::where('school_id', Auth::guard('schools')->id())->findOrFail($id);
        $employee->delete();

        toast('Data guru/staff berhasil dihapus', 'success');
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $school = Auth::guard('schools')->user();

        Excel::import(new EmployeeImport($school->id, $school->branch_id), $request->file('file'));

        toast('Data guru/staff berhasil diimpor', 'success');
        return redirect()->route('sch.employee.index');
    }

    public function resetPassword(string $id)
    {
        $employee = Employee::where('school_id', Auth::guard('schools')->id())->findOrFail($id);

        $newPassword = $this->kanditaService->generatePassword();
        $employee->update([
            'password' => Hash::make($newPassword),
        ]);

        toast('Password guru/staff berhasil direset', 'success');
        return response()->json(['success' => true, 'new_password' => $newPassword]);
    }
}
