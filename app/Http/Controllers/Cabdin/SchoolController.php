<?php

namespace App\Http\Controllers\Cabdin;

use App\Models\School;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class SchoolController extends Controller
{
    /**
     * Tampilkan daftar sekolah (DataTables atau view biasa).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = School::where('branch_id', $this->branch()->id)->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn("branch_name", fn($row) => $row->branch->branch_name ?? '-')
                ->addColumn("action", fn($row) => $this->actionButtons($row->id))
                ->rawColumns(["action"])
                ->make(true);
        }

        return view('cabdin.schools.index', [
            'title' => 'Sekolah',
        ]);
    }

    /**
     * Form tambah sekolah baru.
     */
    public function create()
    {
        return view('cabdin.schools.create', [
            'title' => 'Tambah Sekolah',
        ]);
    }

    /**
     * Simpan sekolah baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_npsn'    => ['required', 'string', 'max:50', 'unique:schools,school_npsn'],
            'school_name'    => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'unique:schools,email'],
            'school_phone'   => ['required', 'string', 'max:30'],
            'school_address' => ['required', 'string'],
        ]);

        $defaultPassword = $request->school_npsn . '*';

        School::create([
            'branch_id'      => $this->branch()->id,
            'school_npsn'    => $request->school_npsn,
            'school_name'    => $request->school_name,
            'email'          => $request->email,
            'password'       => Hash::make($defaultPassword),
            'school_phone'   => $request->school_phone,
            'school_address' => $request->school_address,
            'is_active'      => true,
        ]);

        toast('Sekolah berhasil ditambahkan', 'success');
        return redirect()->route('cabdin.schools.index');
    }

    /**
     * Form edit sekolah.
     */
    public function edit(School $school)
    {
        $this->ensureOwnedByBranch($school);

        return view('cabdin.schools.edit', [
            'title'  => 'Edit Sekolah',
            'school' => $school,
        ]);
    }

    /**
     * Update data sekolah.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'school_npsn'    => ['required', 'string', 'max:10', Rule::unique('schools')->ignore($id)],
            'school_name'    => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', Rule::unique('schools')->ignore($id)],
            'school_phone'   => ['required', 'string', 'max:13'],
            'school_address' => ['required', 'string'],
        ]);

        $school = School::findOrFail($id);
        $this->ensureOwnedByBranch($school);

        // Password logic
        if ($request->filled('password')) {
            $password = Hash::make($request->password);
        } else {
            $password = $school->password; // tidak diubah
        }

        $school->update([
            'branch_id'      => $this->branch()->id,
            'school_npsn'    => $request->school_npsn,
            'school_name'    => $request->school_name,
            'email'          => $request->email,
            'password'       => $password,
            'school_phone'   => $request->school_phone,
            'school_address' => $request->school_address,
            'is_active'      => true,
        ]);

        toast('Data sekolah berhasil diperbarui', 'success');
        return redirect()->route('cabdin.schools.index');
    }

    /**
     * Aktifkan/nonaktifkan sekolah.
     */
    public function toggleStatus(School $school): RedirectResponse
    {
        $this->ensureOwnedByBranch($school);

        $school->update([
            'is_active' => ! $school->is_active,
        ]);

        $message = $school->is_active ? 'Akun sekolah diaktifkan.' : 'Akun sekolah dinonaktifkan.';
        toast($message, 'success');

        return redirect()->route('cabdin.schools.index');
    }

    /**
     * Reset password sekolah.
     */
    public function resetPassword(Request $request, School $school): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureOwnedByBranch($school);

        $newPassword = $this->generatePassword();

        $school->update([
            'password' => Hash::make($newPassword),
        ]);

        // Jika permintaan datang dari AJAX, kembalikan JSON agar bisa ditampilkan di Swal
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Password sekolah berhasil direset.',
                'school'  => [
                    'id'    => $school->id,
                    'name'  => $school->school_name,
                    'email' => $school->email,
                ],
                'password' => $newPassword,
            ]);
        }

        // Fallback non-AJAX
        session()->flash('school_reset_password', [
            'school'   => $school->school_name,
            'email'    => $school->email,
            'password' => $newPassword,
        ]);

        toast('Password sekolah berhasil direset', 'success');
        return redirect()->route('cabdin.schools.index');
    }

    /* ==========================
     * Helper Functions
     * ========================== */

    /**
     * Pastikan sekolah milik branch yang login.
     */
    protected function ensureOwnedByBranch(School $school): void
    {
        if ($school->branch_id !== $this->branch()->id) {
            abort(403, 'Anda tidak berhak mengakses sekolah ini.');
        }
    }

    /**
     * Ambil data branch yang sedang login.
     */
    protected function branch()
    {
        return Auth::guard('branches')->user();
    }

    /**
     * Generate password acak: 3 huruf kapital + 4 angka.
     */
    protected function generatePassword(): string
    {
        return Str::upper(Str::random(3)) . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tombol aksi (reset, edit, delete).
     */
    protected function actionButtons(int $id): string
    {
        $reset = '<button type="button" class="btn btn-outline-danger btn-sm reset ml-2" data-id="'.$id.'">
                    <i class="fas fa-fw fa-sync"></i> Reset
                  </button>';

        $edit = '<button type="button" class="btn btn-outline-primary btn-sm edit ml-2" data-id="'.$id.'">
                    <i class="fas fa-fw fa-pencil-alt"></i> Edit
                 </button>';

        return $reset . $edit;
    }
}
