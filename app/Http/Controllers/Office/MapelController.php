<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Question;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class MapelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Subject::all();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn("action", function ($row) {
                    $showButton = '<button type="button" class="btn btn-outline-info btn-sm show ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-eye"></i> Lihat
                                    </button>';
                    $editButton = '<button type="button" class="btn btn-outline-primary btn-sm edit ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-pencil-alt"></i> Edit
                                    </button>';
                    $deleteButton = '<button type="button" class="btn btn-outline-danger btn-sm delete ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-times"></i> Hapus
                                    </button>';

                    return $showButton . $editButton . $deleteButton;
                })
            ->rawColumns(["action"])
            ->make(true);
        }
        return view('office.mapel.index',[
            'title' => 'Data Mata Pelajaran',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.mapel.add',[
            'title' => 'Tambah Mata Pelajaran',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_name' => 'required',
            'subject_code' => 'required',
        ]);
        Subject::create($request->all());
        toast('Mata Pelajaran berhasil ditambahkan', 'success');
        return redirect()->route('mapel.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        if ($request->ajax()) {
            $data = Question::where('subject_id', $id)->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn("subject_name", function ($row) {
                    return $row->subject->subject_name;
                })
                ->addColumn("action", function ($row) {
                    $showButton = '<button type="button" class="btn btn-outline-info btn-sm show ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-eye"></i> Lihat
                                    </button>';
                    $editButton = '<button type="button" class="btn btn-outline-primary btn-sm edit ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-pencil-alt"></i> Edit
                                    </button>';
                    $deleteButton = '<button type="button" class="btn btn-outline-danger btn-sm delete ml-2" data-id="' . $row->id . '">
                                    <i class="fas fa-fw fa-times"></i> Hapus
                                    </button>';

                    return $showButton . $editButton . $deleteButton;
                })
            ->rawColumns(["action", "subject_name"])
            ->make(true);
        }
        return view('office.soal.index',[
            'title' => 'Data Mata Pelajaran',
            'subject' => Subject::find($id),
            'soal' => Question::where('subject_id', $id)->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('office.mapel.edit',[
            'title' => 'Edit Mata Pelajaran',
            'mapel' => Subject::find($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'subject_name' => 'required',
            'subject_code' => 'required',
        ]);
        Subject::find($id)->update($request->all());
        toast()->success('Mata Pelajaran berhasil diubah');
        return redirect()->route('mapel.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Subject::find($id)->delete();
        return response()->json(['success' => true]);
    }
}
