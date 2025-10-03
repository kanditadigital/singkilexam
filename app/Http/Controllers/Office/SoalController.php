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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        $rules = $this->buildQuestionRules($request);
        $request->validate($rules);
        $this->validateQuestionPayload($request);

        $questionImagePath = null;
        if ($request->hasFile('question_image')) {
            $questionImagePath = $request->file('question_image')->store('questions', 'public');
        }

        $slug = $request->question_text
            ? Str::slug($request->question_text)
            : Str::slug($request->question_category . '-' . time());

        DB::transaction(function () use ($request, $questionImagePath, $slug) {
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

            $this->syncQuestionOptions($question, $request);
        });

        toast('Soal berhasil ditambahkan', 'success');
        return redirect()->route('disdik.mapel.show', $request->subject_id);
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

    $rules = $this->buildQuestionRules($request);
    $request->validate($rules);
    $this->validateQuestionPayload($request);

    // Handle question image
    $questionImagePath = $question->question_image;
    $oldQuestionImage = $question->question_image;
    if ($request->hasFile('question_image')) {
        $questionImagePath = $request->file('question_image')->store('questions', 'public');
    }

    $slug = $request->question_text
        ? Str::slug($request->question_text)
        : Str::slug($request->question_category . '-' . time());

    DB::transaction(function () use ($request, $question, $questionImagePath, $slug) {
        $question->update([
            'subject_id' => $request->subject_id ?? $question->subject_id,
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

        $this->syncQuestionOptions($question, $request, $existingOptions);
    });

    if ($request->hasFile('question_image') && $oldQuestionImage && $oldQuestionImage !== $questionImagePath && Storage::disk('public')->exists($oldQuestionImage)) {
        Storage::disk('public')->delete($oldQuestionImage);
    }

    toast('Soal berhasil diubah', 'success');
    return redirect()->route('disdik.soal.index', ['subject_id' => $request->subject_id ?? $question->subject_id]);
}


    /**
     * Build base validation rules for question payload.
     */
    private function buildQuestionRules(Request $request): array
    {
        $usesTfStatements = $this->usesTrueFalseStatements($request);

        $rules = [
            'subject_id' => 'required',
            'question_category' => 'required',
            'question_type' => 'required|in:multiple_choice,true_false,multiple_response,tkp,matching',
            'question_format' => 'required|in:text,image,text_image',
        ];

        if ($request->question_type === 'matching' || ($request->question_type === 'true_false' && $usesTfStatements)) {
            $rules['option_format'] = 'nullable|in:text,image,text_image';
        } else {
            $rules['option_format'] = 'required|in:text,image,text_image';
        }

        if ($request->question_type === 'true_false') {
            $rules['tf_statements'] = 'array';
            $rules['tf_statements.*'] = 'nullable|string';
            $rules['tf_statement_images'] = 'nullable|array';
            $rules['tf_statement_images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            $rules['tf_correct'] = 'array';
        }

        if (
            in_array($request->question_type, ['multiple_choice', 'multiple_response', 'tkp'], true)
            || ($request->question_type === 'true_false' && !$usesTfStatements)
        ) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'nullable|string';
            $rules['option_images'] = 'nullable|array';
            $rules['option_images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        if ($request->question_type === 'multiple_choice') {
            $rules['correct_answer'] = 'required';
        } elseif ($request->question_type === 'true_false' && !$usesTfStatements) {
            $rules['correct_answer'] = 'required';
        }

        if ($request->question_type === 'multiple_response') {
            $rules['correct_answer'] = 'required|array|min:1';
        }

        if ($request->question_type === 'tkp') {
            $rules['option_scores'] = 'required|array|min:2';
            $rules['option_scores.*'] = 'nullable|numeric|min:0|max:100';
        }

        if ($request->question_type === 'matching') {
            $rules['left_items'] = 'required|array|min:2';
            $rules['left_items.*'] = 'required|string';
            $rules['right_items'] = 'required|array|min:2';
            $rules['right_items.*'] = 'required|string';
            $rules['matches'] = 'required|array';
        }

        return $rules;
    }

    /**
     * Additional payload validation after base rules pass.
     */
    private function validateQuestionPayload(Request $request): void
    {
        $type = $request->question_type;

        if ($type === 'matching') {
            $leftItems = array_values($request->input('left_items', []));
            $rightItems = array_values($request->input('right_items', []));
            $matches = array_values($request->input('matches', []));

            if (count($leftItems) < 2 || count($rightItems) < 2) {
                throw ValidationException::withMessages([
                    'left_items' => 'Minimal harus ada dua item di setiap kolom.',
                ]);
            }

            if (count($matches) !== count($leftItems)) {
                throw ValidationException::withMessages([
                    'matches' => 'Setiap item kiri harus dipasangkan dengan satu pilihan.',
                ]);
            }

            foreach ($matches as $index => $match) {
                if ($match === '' || $match === null) {
                    throw ValidationException::withMessages([
                        'matches' => 'Semua item kiri harus memiliki pasangan.',
                    ]);
                }

                $matchIndex = (int) $match;
                if (!array_key_exists($matchIndex, $rightItems)) {
                    throw ValidationException::withMessages([
                        'matches' => 'Pasangan item kiri tidak valid.',
                    ]);
                }
            }

            return;
        }

        if ($type === 'true_false' && $this->usesTrueFalseStatements($request)) {
            $entries = $this->extractTrueFalseEntries($request);

            if (empty($entries)) {
                throw ValidationException::withMessages([
                    'tf_statements' => 'Isi minimal satu pernyataan True/False.',
                ]);
            }

            $missingDecision = collect($entries)->firstWhere('has_decision', false);
            if ($missingDecision) {
                throw ValidationException::withMessages([
                    'tf_correct' => 'Tentukan Benar atau Salah untuk setiap pernyataan.',
                ]);
            }

            return;
        }

        if (in_array($type, ['multiple_choice', 'true_false', 'multiple_response', 'tkp'])) {
            $options = array_values($request->input('options', []));
            if (count($options) < 2) {
                throw ValidationException::withMessages([
                    'options' => 'Minimal harus ada dua pilihan jawaban.',
                ]);
            }

            if (in_array($type, ['multiple_choice', 'true_false'])) {
                if (!$request->filled('correct_answer')) {
                    throw ValidationException::withMessages([
                        'correct_answer' => 'Pilih jawaban yang benar.',
                    ]);
                }
            }

            if ($type === 'multiple_response') {
                $corrects = array_filter((array) $request->input('correct_answer', []) , fn ($value) => $value !== null && $value !== '');
                if (empty($corrects)) {
                    throw ValidationException::withMessages([
                        'correct_answer' => 'Pilih minimal satu jawaban yang benar.',
                    ]);
                }
            }

            if ($type === 'tkp') {
                $scores = array_values($request->input('option_scores', []));
                if (count($scores) !== count($options)) {
                    throw ValidationException::withMessages([
                        'option_scores' => 'Setiap pilihan harus memiliki bobot nilai.',
                    ]);
                }
            }
        }
    }

    /**
     * Persist question options according to question type.
     */
    private function syncQuestionOptions(Question $question, Request $request, ?Collection $existingOptions = null): void
    {
        $existingOptions = $existingOptions ?? collect();
        $preservedImages = [];

        if ($request->question_type === 'matching') {
            $this->handleMatchingOptions($question, $request);
        } elseif ($request->question_type === 'true_false' && $this->usesTrueFalseStatements($request, $existingOptions)) {
            $preservedImages = $this->handleTrueFalseStatements($question, $request, $existingOptions);
        } else {
            $preservedImages = $this->handleStandardOptions($question, $request, $existingOptions);
        }

        $this->cleanupUnusedOptionImages($existingOptions, $preservedImages);
    }

    /**
     * Handle options for standard question types (multiple choice, multiple response, true/false, tkp).
     */
    private function handleStandardOptions(Question $question, Request $request, Collection $existingOptions): array
    {
        $optionTexts = array_values($request->input('options', []));
        $optionImagesInput = $request->file('option_images', []);
        if (!is_array($optionImagesInput)) {
            $optionImagesInput = [];
        }
        $optionImages = array_values($optionImagesInput);
        $optionScores = array_values($request->input('option_scores', []));

        $optionFormat = $request->option_format ?? 'text';
        $questionType = $request->question_type;

        $correctRaw = [];
        if ($questionType === 'multiple_response') {
            $correctRaw = (array) $request->input('correct_answer', []);
        } else {
            $value = $request->input('correct_answer');
            if ($value !== null && $value !== '') {
                $correctRaw = [$value];
            }
        }

        $correctIndices = collect($correctRaw)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->all();

        $preservedImages = [];

        foreach ($optionTexts as $index => $rawText) {
            $existing = $existingOptions->get($index);
            $imageFile = $optionImages[$index] ?? null;
            $imagePath = null;

            if ($imageFile && $imageFile->isValid()) {
                if ($existing && $existing->option_image) {
                    $this->deleteOptionImage($existing->option_image);
                }
                $imagePath = $imageFile->store('question_options', 'public');
            } elseif ($existing && $existing->option_image) {
                $imagePath = $existing->option_image;
            }

            if ($imagePath) {
                $preservedImages[] = $imagePath;
            }

            $optionText = null;
            if ($optionFormat !== 'image') {
                $optionText = trim((string) $rawText) !== '' ? $rawText : null;
            }

            $isCorrect = false;
            if (in_array($questionType, ['multiple_choice', 'multiple_response', 'true_false'], true)) {
                $isCorrect = in_array($index + 1, $correctIndices, true);
            }

            $score = 0;
            if ($questionType === 'tkp') {
                $score = max(0, min(100, (int) ($optionScores[$index] ?? 0)));
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => $this->makeOptionLabel($index),
                'option_key' => null,
                'option_text' => $optionText,
                'option_image' => $imagePath,
                'is_correct' => $isCorrect,
                'score' => $score,
            ]);
        }

        return $preservedImages;
    }

    /**
     * Handle True/False statements (grid format).
     */
    private function handleTrueFalseStatements(Question $question, Request $request, ?Collection $existingOptions = null): array
    {
        $entries = $this->extractTrueFalseEntries($request, $existingOptions);
        $existing = $existingOptions ? $existingOptions->values() : collect();
        $preservedImages = [];

        foreach ($entries as $position => $entry) {
            $imagePath = null;
            $existingOption = $existing->get($entry['original_index']);

            if ($entry['image_file']) {
                if ($existingOption && $existingOption->option_image) {
                    $this->deleteOptionImage($existingOption->option_image);
                }
                $imagePath = $entry['image_file']->store('question_options', 'public');
            } elseif ($entry['existing_image']) {
                $imagePath = $entry['existing_image'];
            }

            if ($imagePath) {
                $preservedImages[] = $imagePath;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'P' . ($position + 1),
                'option_key' => 'statement_' . ($position + 1),
                'option_text' => $entry['text'],
                'option_image' => $imagePath,
                'is_correct' => $entry['is_true'],
                'score' => $entry['is_true'] ? 1 : 0,
            ]);
        }

        return $preservedImages;
    }

    /**
     * Handle matching question options.
     */
    private function handleMatchingOptions(Question $question, Request $request): void
    {
        $leftItems = array_values($request->input('left_items', []));
        $rightItems = array_values($request->input('right_items', []));
        $matches = array_values($request->input('matches', []));

        foreach ($leftItems as $index => $text) {
            $matchIndex = (int) ($matches[$index] ?? -1);
            $optionKey = $matchIndex >= 0 && isset($rightItems[$matchIndex])
                ? 'R' . ($matchIndex + 1)
                : null;

            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'L' . ($index + 1),
                'option_key' => $optionKey,
                'option_text' => $text ?: null,
                'is_correct' => false,
                'score' => 0,
            ]);
        }

        foreach ($rightItems as $index => $text) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_label' => 'R' . ($index + 1),
                'option_key' => null,
                'option_text' => $text ?: null,
                'is_correct' => false,
                'score' => 0,
            ]);
        }
    }

    private function usesTrueFalseStatements(Request $request, ?Collection $existingOptions = null): bool
    {
        $statements = $request->input('tf_statements');
        if (is_array($statements)) {
            foreach ($statements as $text) {
                if (trim((string) $text) !== '') {
                    return true;
                }
            }
        }

        if ($request->hasFile('tf_statement_images')) {
            foreach ((array) $request->file('tf_statement_images') as $file) {
                if ($file && $file->isValid()) {
                    return true;
                }
            }
        }

        if ($existingOptions) {
            foreach ($existingOptions as $option) {
                if (Str::startsWith((string) $option->option_label, 'P')) {
                    return true;
                }
            }
        }

        return false;
    }

    private function extractTrueFalseEntries(Request $request, ?Collection $existingOptions = null): array
    {
        $statements = $request->input('tf_statements', []);
        $corrects = $request->input('tf_correct', []);
        $imagesInput = $request->file('tf_statement_images', []);
        if (!is_array($imagesInput)) {
            $imagesInput = [];
        }
        $existing = $existingOptions ? $existingOptions->values() : collect();

        $entries = [];

        foreach ((array) $statements as $index => $rawText) {
            $text = trim((string) $rawText);
            $imageFile = $imagesInput[$index] ?? null;
            $existingOption = $existing->get($index);
            $existingImage = $existingOption ? $existingOption->option_image : null;

            $hasImageUpload = $imageFile && $imageFile->isValid();
            $hasExistingImage = !$hasImageUpload && $existingImage;
            $hasText = $text !== '';

            if (!$hasText && !$hasImageUpload && !$hasExistingImage) {
                continue;
            }

            $hasDecision = array_key_exists($index, $corrects);
            $isTrue = $hasDecision && in_array($corrects[$index], ['true', '1', 1, true, 'on'], true);

            $entries[] = [
                'original_index' => (int) $index,
                'text' => $text !== '' ? $text : null,
                'image_file' => $hasImageUpload ? $imageFile : null,
                'existing_image' => $hasImageUpload ? null : ($hasExistingImage ? $existingImage : null),
                'is_true' => $isTrue,
                'has_decision' => $hasDecision,
            ];
        }

        return $entries;
    }

    /**
     * Delete unused option images from storage.
     */
    private function cleanupUnusedOptionImages(Collection $existingOptions, array $preservedImages): void
    {
        $preserved = array_filter($preservedImages, fn ($path) => !empty($path));

        $existingOptions->each(function ($option) use ($preserved) {
            if (!empty($option->option_image) && !in_array($option->option_image, $preserved, true)) {
                $this->deleteOptionImage($option->option_image);
            }
        });
    }

    /**
     * Remove image file from storage if exists.
     */
    private function deleteOptionImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Generate human-readable option label (A, B, ..., AA, AB, ...).
     */
    private function makeOptionLabel(int $index): string
    {
        $index += 1; // convert to 1-based index
        $label = '';

        while ($index > 0) {
            $index--;
            $label = chr(65 + ($index % 26)) . $label;
            $index = intdiv($index, 26);
        }

        return $label;
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
