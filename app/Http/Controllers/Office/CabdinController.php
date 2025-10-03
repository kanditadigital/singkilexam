<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class CabdinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Branch::all();

            return DataTables::of($data)
                ->addIndexColumn()
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
        return view('office.cabdin.index',[
            'title' => 'Cabang Dinas Pendidikan'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.cabdin.add',[
            'title' => 'Tambah Cabang Dinas Pendidikan'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_name'       => 'required',
            'email'             => 'required|email|unique:branches',
            'branch_phone'      => 'required',
            'branch_address'    => 'required',
        ]);

        Branch::create([
            'branch_name'       => $request->branch_name,
            'email'             => $request->email,
            'password'          => Hash::make('cabdin*'),
            'branch_phone'      => $request->branch_phone,
            'branch_address'    => $request->branch_address,
        ]);
        toast('Cabang Dinas Pendidikan berhasil ditambahkan', 'success');
        return redirect()->route('disdik.cabdin.index');
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
        return view('office.cabdin.edit',[
            'title' => 'Edit Cabang Dinas Pendidikan',
            'cabdin' => Branch::find($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'branch_name'       => 'required',
            'email'             => 'nullable|email',
            'branch_phone'      => 'required',
            'branch_address'    => 'required',
        ]);
        Branch::where('id', $id)->update([
            'branch_name'       => $request->branch_name,
            'email'             => $request->email,
            'branch_phone'      => $request->branch_phone,
            'branch_address'    => $request->branch_address,
        ]);
        toast('Cabang Dinas Pendidikan berhasil diubah', 'success');
        return redirect()->route('disdik.cabdin.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Branch::where('id', $id)->delete();
        toast('Cabang Dinas Pendidikan berhasil dihapus', 'success');
        return response()->json(['success' => true]);
    }
}
