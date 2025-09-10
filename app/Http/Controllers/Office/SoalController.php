<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Str;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question_option;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $subjectId = $request->route('id');
            $data = Question::where('subject_id', $subjectId)->with('subject')->get();

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
            ->rawColumns(["action", "subject_name", "session_number", "exam_name"])
            ->make(true);
        }
        return view('office.soal.index',[
            'title' => 'Data Soal',
            'subject' => Subject::find($request->route('id')),
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
        // Dynamic validation based on question and option formats
        $rules = [
            'subject_id' => 'required',
            'question_category' => 'required|in:Literasi,Numerasi,Teknis,Pedagogik,TKP',
            'question_type' => 'required|in:multiple_choice,true_false,multiple_response,tkp,matching',
            'question_format' => 'required|in:text,image,text_image',
            'option_format' => 'required|in:text,image,text_image',
        ];
        
        // Only require options for non-matching questions
        if ($request->question_type !== 'matching') {
            $rules['options'] = 'required|array|min:2';
        }

        // Add conditional validation based on question format
        if ($request->question_format === 'text' || $request->question_format === 'text_image') {
            $rules['question_text'] = 'required|string';
        }
        
        if ($request->question_format === 'image' || $request->question_format === 'text_image') {
            $rules['question_image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        // Add validation for option images based on option format (skip for matching)
        if ($request->question_type !== 'matching' && ($request->option_format === 'image' || $request->option_format === 'text_image')) {
            $rules['option_images.*'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        // Add validation for correct answers based on question type
        if ($request->question_type === 'multiple_response') {
            $rules['correct_answer'] = 'required|array|min:1';
        } elseif ($request->question_type === 'tkp') {
            $rules['option_scores'] = 'required|array';
            $rules['option_scores.*'] = 'required|numeric|min:0|max:100';
        } elseif ($request->question_type === 'matching') {
            $rules['left_items'] = 'required|array|min:2';
            $rules['left_items.*'] = 'required|string|max:255';
            $rules['right_items'] = 'required|array|min:2';
            $rules['right_items.*'] = 'required|string|max:255';
            $rules['matches'] = 'required|array|min:2';
            $rules['matches.*'] = 'required|integer|min:0';
        } else {
            $rules['correct_answer'] = 'required';
        }

        $request->validate($rules);

        // Handle question image upload if present
        $questionImagePath = null;
        if ($request->hasFile('question_image')) {
            $questionImagePath = $request->file('question_image')->store('questions', 'public');
        }

        // Create slug from question_text if available, otherwise use category + timestamp
        $slug = $request->question_text 
            ? Str::slug($request->question_text) 
            : Str::slug($request->question_category . '-' . time());

        // Create the question
        $question = Question::create([
            'subject_id' => $request->subject_id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'question_category' => $request->question_category,
            'question_format' => $request->question_format,
            'option_format' => $request->option_format,
            'question_image' => $questionImagePath,
            'slug' => $slug,
        ]);

        // Create question options
        $optionLabels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        
        if ($request->question_type === 'tkp') {
            // For TKP questions, use custom scores for each option
            foreach ($request->options as $index => $optionText) {
                $optionImagePath = null;
                
                // Handle option image upload if present
                if ($request->hasFile("option_images.{$index}")) {
                    $optionImagePath = $request->file("option_images.{$index}")->store('question_options', 'public');
                }

                $score = $request->option_scores[$index] ?? 0;
                
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => $optionLabels[$index] ?? chr(65 + $index), // A, B, C, D, etc.
                    'option_key' => 'option_' . ($index + 1),
                    'option_text' => $optionText === "image_" . ($index + 1) || strpos($optionText, 'image_') === 0 ? null : $optionText,
                    'option_image' => $optionImagePath,
                    'is_correct' => false, // TKP doesn't have correct/incorrect, only scores
                    'score' => $score,
                ]);
            }
        } elseif ($request->question_type === 'matching') {
            // For matching questions, create left items and right items
            $leftItems = $request->input('left_items', []);
            $rightItems = $request->input('right_items', []);
            $matches = $request->input('matches', []);
            
            // Create left items (items to be matched)
            foreach ($leftItems as $index => $leftText) {
                $correctMatch = isset($matches[$index]) ? 'R' . ($matches[$index] + 1) : null;
                
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => 'L' . ($index + 1),
                    'option_key' => $correctMatch, // Store which right item this left item matches
                    'option_text' => $leftText,
                    'option_image' => null,
                    'is_correct' => false,
                    'score' => 0,
                ]);
            }
            
            // Create right items (matching options)
            foreach ($rightItems as $index => $rightText) {
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => 'R' . ($index + 1),
                    'option_key' => 'right_option',
                    'option_text' => $rightText,
                    'option_image' => null,
                    'is_correct' => false,
                    'score' => 0,
                ]);
            }
        } else {
            // For other question types, use the existing logic
            $correctAnswers = is_array($request->correct_answer) ? $request->correct_answer : [$request->correct_answer];
            
            foreach ($request->options as $index => $optionText) {
                $optionImagePath = null;
                
                // Handle option image upload if present
                if ($request->hasFile("option_images.{$index}")) {
                    $optionImagePath = $request->file("option_images.{$index}")->store('question_options', 'public');
                }

                // Check if this option is correct
                $isCorrect = in_array(($index + 1), $correctAnswers);
                
                // For true/false questions, use "Benar"/"Salah" labels instead of A, B
                $optionLabel = '';
                if ($request->question_type === 'true_false') {
                    $optionLabel = ($index === 0) ? 'Benar' : 'Salah';
                } else {
                    $optionLabel = $optionLabels[$index] ?? chr(65 + $index); // A, B, C, D, etc.
                }
                
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => $optionLabel,
                    'option_key' => 'option_' . ($index + 1),
                    'option_text' => $optionText === "image_" . ($index + 1) || strpos($optionText, 'image_') === 0 ? null : $optionText,
                    'option_image' => $optionImagePath,
                    'is_correct' => $isCorrect,
                    'score' => $isCorrect ? 1 : 0,
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

        // Dynamic validation based on question and option formats
        $rules = [
            'question_category' => 'required|in:Literasi,Numerasi,Teknis,Pedagogik,TKP',
            'question_type' => 'required|in:multiple_choice,true_false,multiple_response,tkp,matching',
            'question_format' => 'required|in:text,image,text_image',
            'option_format' => 'required|in:text,image,text_image',
            'options' => 'required|array|min:2',
        ];

        // Add conditional validation based on question format
        if ($request->question_format === 'text' || $request->question_format === 'text_image') {
            $rules['question_text'] = 'required|string';
        }
        
        if ($request->question_format === 'image' || $request->question_format === 'text_image') {
            // Only require image if no existing image and no new image uploaded
            if (!$question->question_image && !$request->hasFile('question_image')) {
                $rules['question_image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            } else {
                $rules['question_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            }
        }

        // Add validation for correct answers based on question type
        if ($request->question_type === 'multiple_response') {
            $rules['correct_answer'] = 'required|array|min:1';
        } elseif ($request->question_type === 'tkp') {
            $rules['option_scores'] = 'required|array';
            $rules['option_scores.*'] = 'required|numeric|min:0|max:100';
        } elseif ($request->question_type === 'matching') {
            $rules['left_items'] = 'required|array|min:2';
            $rules['left_items.*'] = 'required|string|max:255';
            $rules['right_items'] = 'required|array|min:2';
            $rules['right_items.*'] = 'required|string|max:255';
            $rules['matches'] = 'required|array|min:2';
            $rules['matches.*'] = 'required|integer|min:0';
        } else {
            $rules['correct_answer'] = 'required';
        }

        $request->validate($rules);

        // Handle question image upload if present
        $questionImagePath = $question->question_image; // Keep existing image by default
        if ($request->hasFile('question_image')) {
            // Delete old image if exists
            if ($question->question_image && Storage::disk('public')->exists($question->question_image)) {
                Storage::disk('public')->delete($question->question_image);
            }
            $questionImagePath = $request->file('question_image')->store('questions', 'public');
        }

        // Create slug from question_text if available, otherwise use category + timestamp
        $slug = $request->question_text 
            ? Str::slug($request->question_text) 
            : Str::slug($request->question_category . '-' . time());

        // Update the question
        $question->update([
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'question_category' => $request->question_category,
            'question_format' => $request->question_format,
            'option_format' => $request->option_format,
            'question_image' => $questionImagePath,
            'slug' => $slug,
        ]);

        // Delete existing options and their images
        $existingOptions = $question->questionOptions;
        foreach ($existingOptions as $option) {
            if ($option->option_image && Storage::disk('public')->exists($option->option_image)) {
                Storage::disk('public')->delete($option->option_image);
            }
        }
        $question->questionOptions()->delete();

        // Create new question options
        $optionLabels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        
        if ($request->question_type === 'tkp') {
            // For TKP questions, use custom scores for each option
            $optionIndex = 0;
            foreach ($request->options as $optionId => $optionData) {
                $optionImagePath = null;
                
                // Handle option image upload if present
                if ($request->hasFile("options.{$optionId}.image")) {
                    $optionImagePath = $request->file("options.{$optionId}.image")->store('question_options', 'public');
                }

                // Get option text
                $optionText = is_array($optionData) ? ($optionData['text'] ?? null) : $optionData;
                $score = $request->option_scores[$optionIndex] ?? 0;
                
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => $optionLabels[$optionIndex] ?? chr(65 + $optionIndex), // A, B, C, D, etc.
                    'option_key' => 'option_' . ($optionIndex + 1),
                    'option_text' => $optionText,
                    'option_image' => $optionImagePath,
                    'is_correct' => false, // TKP doesn't have correct/incorrect, only scores
                    'score' => $score,
                ]);
                
                $optionIndex++;
            }
        } elseif ($request->question_type === 'matching') {
            // For matching questions, create left items and right items
            $leftItems = $request->input('left_items', []);
            $rightItems = $request->input('right_items', []);
            $matches = $request->input('matches', []);
            
            // Create left items (items to be matched)
            foreach ($leftItems as $index => $leftText) {
                $correctMatch = isset($matches[$index]) ? 'R' . ($matches[$index] + 1) : null;
                
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => 'L' . ($index + 1),
                    'option_key' => $correctMatch, // Store which right item this left item matches
                    'option_text' => $leftText,
                    'option_image' => null,
                    'is_correct' => false,
                    'score' => 0,
                ]);
            }
            
            // Create right items (matching options)
            foreach ($rightItems as $index => $rightText) {
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => 'R' . ($index + 1),
                    'option_key' => 'right_option',
                    'option_text' => $rightText,
                    'option_image' => null,
                    'is_correct' => false,
                    'score' => 0,
                ]);
            }
        } else {
            // For other question types, use the existing logic
            $correctAnswers = is_array($request->correct_answer) ? $request->correct_answer : [$request->correct_answer];
            
            $optionIndex = 0;
            foreach ($request->options as $optionId => $optionData) {
                $optionImagePath = null;
                
                // Handle option image upload if present
                if ($request->hasFile("options.{$optionId}.image")) {
                    $optionImagePath = $request->file("options.{$optionId}.image")->store('question_options', 'public');
                }

                // Get option text
                $optionText = is_array($optionData) ? ($optionData['text'] ?? null) : $optionData;
                
                // Check if this option is correct
                $isCorrect = in_array($optionId, $correctAnswers);
                
                // For true/false questions, use "Benar"/"Salah" labels instead of A, B
                $optionLabel = '';
                if ($request->question_type === 'true_false') {
                    $optionLabel = ($optionIndex === 0) ? 'Benar' : 'Salah';
                } else {
                    $optionLabel = $optionLabels[$optionIndex] ?? chr(65 + $optionIndex); // A, B, C, D, etc.
                }
                
                Question_option::create([
                    'question_id' => $question->id,
                    'option_label' => $optionLabel,
                    'option_key' => 'option_' . ($optionIndex + 1),
                    'option_text' => $optionText,
                    'option_image' => $optionImagePath,
                    'is_correct' => $isCorrect,
                    'score' => $isCorrect ? 1 : 0,
                ]);
                
                $optionIndex++;
            }
        }

        toast('Soal berhasil diubah', 'success');
        return redirect()->route('soal.index', $question->subject_id);
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
