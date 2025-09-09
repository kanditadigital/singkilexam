<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Branch;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SekolahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = School::all();

            return DataTables::of($data)
                ->addIndexColumn()
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
        return view('office.sekolah.index',[
            'title' => 'Sekolah',
            'cabdin' => Branch::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.sekolah.add',[
            'title' => 'Tambah Sekolah',
            'cabdin' => Branch::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id'         => 'required',
            'school_npsn'       => 'required|unique:schools',
            'school_name'       => 'required',
            'email'             => 'required|email|unique:schools',
            'school_phone'      => 'required',
            'school_address'    => 'required',
        ]);

        School::create([
            'branch_id'         => $request->branch_id,
            'school_npsn'       => $request->school_npsn,
            'school_name'       => $request->school_name,
            'email'             => $request->email,
            'school_phone'      => $request->school_phone,
            'school_address'    => $request->school_address,
        ]);

        toast('Sekolah berhasil ditambahkan', 'success');
        return redirect()->route('sekolah.index');
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
        return view('office.sekolah.edit',[
            'title' => 'Edit Sekolah',
            'sekolah' => School::find($id),
            'cabdin' => Branch::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'branch_id'         => 'required',
            'school_npsn'       => 'required|unique:schools,school_npsn,' . $id,
            'school_name'       => 'required',
            'email'             => 'required|email|unique:schools,email,' . $id,
            'school_phone'      => 'required',
            'school_address'    => 'required',
        ]);

        School::where('id', $id)->update([
            'branch_id'         => $request->branch_id,
            'school_npsn'       => $request->school_npsn,
            'school_name'       => $request->school_name,
            'email'             => $request->email,
            'school_phone'      => $request->school_phone,
            'school_address'    => $request->school_address,
        ]);

        toast('Sekolah berhasil diubah', 'success');
        return redirect()->route('sekolah.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        School::where('id', $id)->delete();
        toast('Sekolah berhasil dihapus', 'success');
        return response()->json(['success' => true]);
    }
}
