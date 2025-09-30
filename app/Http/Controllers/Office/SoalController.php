<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Str;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\QuestionOption;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class SoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $subjectId = $request->route('id')
            ?? $request->query('subject_id')
            ?? $request->input('subject_id');

        if ($request->ajax()) {
            $query = Question::with('subject');

            if (!empty($subjectId)) {
                $query->where('subject_id', $subjectId);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn("subject_name", function ($row) {
                    return optional($row->subject)->subject_name ?? '-';
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
            ->rawColumns(["action", "subject_name", "session_number", "exam_name"])
            ->make(true);
        }
        return view('office.soal.index',[
            'title' => 'Data Soal',
            'subject' => $subjectId ? Subject::find($subjectId) : null,
            'subjectId' => $subjectId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('office.soal.add',[
            'title' => 'Tambah Soal',
            'exam' => Exam::all(),
            'exam_session' => ExamSession::all(),
            'subject' => Subject::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $rules = [
        'subject_id' => 'required',
        'question_category' => 'required',
        'question_type' => 'required|in:multiple_choice,true_false,multiple_response,tkp,matching',
        'question_format' => 'required|in:text,image,text_image',
    ];
    if ($request->question_type === 'matching' || ($request->question_type === 'true_false' && $request->has('tf_statements'))) {
        $rules['option_format'] = 'nullable|in:text,image,text_image';
    } else {
        $rules['option_format'] = 'required|in:text,image,text_image';
    }
    if ($request->question_type === 'true_false' && $request->has('tf_statements')) {
        $rules['tf_statements'] = 'required|array|min:1';
        $rules['tf_statements.*'] = 'nullable|string';
        $rules['tf_statement_images'] = 'nullable|array';
        $rules['tf_statement_images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        $rules['tf_correct'] = 'required|array|min:1';
    }

    $request->validate($rules);

    // Handle question image
    $questionImagePath = null;
    if ($request->hasFile('question_image')) {
        $questionImagePath = $request->file('question_image')->store('questions', 'public');
    }

    $slug = $request->question_text
        ? Str::slug($request->question_text)
        : Str::slug($request->question_category . '-' . time());

    $question = Question::create([
        'subject_id' => $request->subject_id,
        'question_text' => $request->question_text,
        'question_type' => $request->question_type,
        'question_category' => $request->question_category,
        'question_format' => $request->question_format,
        'option_format' => $request->option_format ?? 'text',
        'question_image' => $questionImagePath,
        'slug' => $slug,
    ]);

    // === TRUE/FALSE dengan tf_statements (PERBAIKAN) ===
    if ($request->question_type === 'true_false' && $request->has('tf_statements')) {
        $statements = $request->input('tf_statements', []);
        $corrects   = $request->input('tf_correct', []);
        $images     = $request->file('tf_statement_images', []);

        foreach ($statements as $i => $text) {
            $isTrue = isset($corrects[$i]) && in_array($corrects[$i], ['true','1',1,true], true);

            $optionImagePath = null;
            if (isset($images[$i]) && $images[$i]->isValid()) {
                $optionImagePath = $images[$i]->store('question_options', 'public');
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'P' . ($i + 1),
                'option_key' => 'statement_' . ($i + 1),
                'option_text' => $text ?: null,
                'option_image' => $optionImagePath,
                'is_correct' => $isTrue,
                'score' => $isTrue ? 1 : 0,
            ]);
        }
    }

    toast('Soal berhasil ditambahkan', 'success');
    return redirect()->route('mapel.show', $request->subject_id);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('office.soal.index',[
            'title' => 'Data Soal',
            'soal' => Question::find($id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $question = Question::with('questionOptions')->find($id);

        if (!$question) {
            toast('Soal tidak ditemukan', 'error');
            return redirect()->back();
        }

        return view('office.soal.edit',[
            'title' => 'Edit Soal',
            'soal' => $question,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $question = Question::find($id);
    if (!$question) {
        toast('Soal tidak ditemukan', 'error');
        return redirect()->back();
    }

    $rules = [
        'question_category' => 'required',
        'question_type' => 'required|in:multiple_choice,true_false,multiple_response,tkp,matching',
        'question_format' => 'required|in:text,image,text_image',
    ];
    if ($request->question_type === 'true_false' && $request->has('tf_statements')) {
        $rules['tf_statements'] = 'required|array|min:1';
        $rules['tf_statements.*'] = 'nullable|string';
        $rules['tf_statement_images'] = 'nullable|array';
        $rules['tf_statement_images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        $rules['tf_correct'] = 'required|array|min:1';
    }

    $request->validate($rules);

    // Handle question image
    $questionImagePath = $question->question_image;
    if ($request->hasFile('question_image')) {
        if ($question->question_image && Storage::disk('public')->exists($question->question_image)) {
            Storage::disk('public')->delete($question->question_image);
        }
        $questionImagePath = $request->file('question_image')->store('questions', 'public');
    }

    $slug = $request->question_text
        ? Str::slug($request->question_text)
        : Str::slug($request->question_category . '-' . time());

    $question->update([
        'question_text' => $request->question_text,
        'question_type' => $request->question_type,
        'question_category' => $request->question_category,
        'question_format' => $request->question_format,
        'option_format' => $request->option_format ?? 'text',
        'question_image' => $questionImagePath,
        'slug' => $slug,
    ]);

    $existingOptions = $question->questionOptions()->get();
    $question->questionOptions()->delete();

    // === TRUE/FALSE dengan tf_statements (PERBAIKAN) ===
    if ($request->question_type === 'true_false' && $request->has('tf_statements')) {
        $statements = $request->input('tf_statements', []);
        $corrects   = $request->input('tf_correct', []);
        $images     = $request->file('tf_statement_images', []);
        $existing   = $existingOptions->values();

        foreach ($statements as $i => $text) {
            $isTrue = isset($corrects[$i]) && in_array($corrects[$i], ['true','1',1,true], true);

            $optionImagePath = null;
            if (isset($images[$i]) && $images[$i]->isValid()) {
                if (isset($existing[$i]) && $existing[$i]->option_image && Storage::disk('public')->exists($existing[$i]->option_image)) {
                    Storage::disk('public')->delete($existing[$i]->option_image);
                }
                $optionImagePath = $images[$i]->store('question_options', 'public');
            } elseif (isset($existing[$i])) {
                $optionImagePath = $existing[$i]->option_image; // gunakan gambar lama
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'P' . ($i + 1),
                'option_key' => 'statement_' . ($i + 1),
                'option_text' => $text ?: null,
                'option_image' => $optionImagePath,
                'is_correct' => $isTrue,
                'score' => $isTrue ? 1 : 0,
            ]);
        }
    }

    toast('Soal berhasil diubah', 'success');
    return redirect()->route('soal.index', ['subject_id' => $question->subject_id]);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Question::where('id', $id)->delete();
        toast('Soal berhasil dihapus', 'success');
        return response()->json(['success' => true]);
    }
}
