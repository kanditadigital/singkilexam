<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Services\KanditaService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UjianController extends Controller
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
            $data = Exam::all();
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
            ->rawColumns(["action",])
            ->make(true);
        }
        return view('office.exam.index',[
            'title' => 'Daftar Ujian',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.exam.add',[
            'title' => 'Tambah Ujian',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_type'         => 'required',
            'exam_name'         => 'required',
            'exam_description'  => 'required',
        ]);

        Exam::create([
            'exam_type'         => $request->exam_type,
            'exam_name'         => $request->exam_name,
            'exam_description'  => $request->exam_description,
            'exam_code'         => $this->kanditaService->generateExamCode(),
        ]);

        toast('Ujian berhasil ditambahkan', 'success');
        return redirect()->route('exam.index');
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
        $exam = Exam::find($id);
        return view('office.exam.edit',[
            'title' => 'Edit Ujian',
            'exam' => $exam,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'exam_type'         => 'required',
            'exam_name'         => 'required',
            'exam_description'  => 'required',
            'exam_status'       => 'required',
        ]);

        Exam::where('id', $id)->update([
            'exam_type'         => $request->exam_type,
            'exam_name'         => $request->exam_name,
            'exam_description'  => $request->exam_description,
            'exam_status'       => $request->exam_status,
        ]);

        toast('Ujian berhasil diubah', 'success');
        return redirect()->route('exam.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $exam = Exam::find($id);
        $exam->delete();
        return response()->json(['success' => true]);
    }
}
