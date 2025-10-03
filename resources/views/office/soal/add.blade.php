@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-question"></i> Tambah Soal</h4>
                <div class="ml-auto">
                    <a href="javascript:history.back()" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('disdik.soal.store') }}" method="post" enctype="multipart/form-data" id="question-form">
                    @csrf
                    <input type="hidden" name="subject_id" id="subject_id" value="{{ old('subject_id', request('subject_id')) }}">
                    <input type="hidden" name="question_format_hidden" id="question_format_hidden" value="{{ old('question_format') }}">
                    <input type="hidden" name="option_format_hidden" id="option_format_hidden" value="{{ old('option_format') }}">

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="question_category">Kategori Soal <span class="text-danger">*</span></label>
                            <select name="question_category" id="question_category" class="form-control custom-select @error('question_category') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Bahasa Indonesia" {{ old('question_category') == 'Bahasa Indonesia' ? 'selected' : '' }}>Bahasa Indonesia</option>
                                <option value="Bahasa Inggris" {{ old('question_category') == 'Bahasa Inggris' ? 'selected' : '' }}>Bahasa Inggris</option>
                                <option value="Matematika" {{ old('question_category') == 'Matematika' ? 'selected' : '' }}>Matematika</option>
                                <option value="Literasi" {{ old('question_category') == 'Literasi' ? 'selected' : '' }}>Literasi</option>
                                <option value="Numerasi" {{ old('question_category') == 'Numerasi' ? 'selected' : '' }}>Numerasi</option>
                                <option value="Teknis" {{ old('question_category') == 'Teknis' ? 'selected' : '' }}>Teknis</option>
                                <option value="Pedagogik" {{ old('question_category') == 'Pedagogik' ? 'selected' : '' }}>Pedagogik</option>
                            </select>
                            @error('question_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="question_type">Tipe Soal <span class="text-danger">*</span></label>
                            <select name="question_type" id="question_type" class="form-control custom-select @error('question_type') is-invalid @enderror" required>
                                <option value="">Pilih Tipe</option>
                                <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                <option value="true_false" {{ old('question_type') == 'true_false' ? 'selected' : '' }}>True/False</option>
                                <option value="multiple_response" {{ old('question_type') == 'multiple_response' ? 'selected' : '' }}>Multiple Response</option>
                                <option value="tkp" {{ old('question_type') == 'tkp' ? 'selected' : '' }}>Bobot Per Soal</option>
                                <option value="matching" {{ old('question_type') == 'matching' ? 'selected' : '' }}>Matching (Menjodohkan)</option>
                            </select>
                            @error('question_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="question_format">Format Soal <span class="text-danger">*</span></label>
                            <select name="question_format" id="question_format" class="form-control custom-select @error('question_format') is-invalid @enderror" required>
                                <option value="">Pilih Format</option>
                                <option value="text" {{ old('question_format') == 'text' ? 'selected' : '' }}>Teks</option>
                                <option value="image" {{ old('question_format') == 'image' ? 'selected' : '' }}>Gambar</option>
                                <option value="text_image" {{ old('question_format') == 'text_image' ? 'selected' : '' }}>Teks + Gambar</option>
                            </select>
                            @error('question_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="option_format">Format Pilihan Jawaban <span class="text-danger">*</span></label>
                            <select name="option_format" id="option_format" class="form-control custom-select @error('option_format') is-invalid @enderror" required>
                                <option value="">Pilih Format</option>
                                <option value="text" {{ old('option_format') == 'text' ? 'selected' : '' }}>Teks</option>
                                <option value="image" {{ old('option_format') == 'image' ? 'selected' : '' }}>Gambar</option>
                                <option value="text_image" {{ old('option_format') == 'text_image' ? 'selected' : '' }}>Teks + Gambar</option>
                            </select>
                            @error('option_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row" id="question-text-container">
                        <div class="col-md-12">
                            <label for="question_text">Pertanyaan <span class="text-danger" id="text-required">*</span></label>
                            <textarea name="question_text" id="texteditor" class="@error('question_text') is-invalid @enderror">{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row" id="question-image-container" style="display: none;">
                        <div class="col-md-12">
                            <label for="question_image">Gambar Soal <span class="text-danger" id="image-required">*</span></label>
                            <input type="file" name="question_image" id="question_image" class="form-control @error('question_image') is-invalid @enderror" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml">
                            @error('question_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Format yang diizinkan: JPEG, JPG, PNG, GIF, SVG. Maksimal 2MB.</small>
                            <div id="image-preview" class="mt-2" style="display: none;">
                                <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                <button type="button" id="remove-image" class="btn btn-sm btn-danger ml-2">
                                    <i class="fas fa-times"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Options Container (Multiple choice, Multiple response, TKP) -->
                    <div id="options-container" style="display: none;">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label>Pilihan Jawaban <span class="text-danger">*</span></label>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted" id="options-instruction">Tambahkan pilihan jawaban untuk soal</small>
                                    <button type="button" id="add-option" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Tambah Pilihan
                                    </button>
                                </div>
                                <div id="options-list">
                                    <!-- Options will be dynamically added here -->
                                </div>
                                <div class="alert alert-danger mt-2" id="correct-answer-info" style="display: none;">
                                    <small><i class="fas fa-info-circle"></i> <span id="text-white">Pilih salah satu opsi sebagai jawaban yang benar</span></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- True/False Grid Container (Statements) -->
                    <div id="truefalse-container" style="display:none;">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label>Pernyataan True/False <span class="text-danger">*</span></label>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Tambah pernyataan. Tentukan mana yang Benar atau Salah.</small>
                                    <button type="button" id="add-tf" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Tambah Pernyataan
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:60%">Pernyataan</th>
                                                <th class="text-center" style="width:15%">Benar</th>
                                                <th class="text-center" style="width:15%">Salah</th>
                                                <th class="text-center" style="width:10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tf-list">
                                            <!-- Rows by JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Matching Container -->
                    <div id="matching-container" style="display: none;">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label>Pasangan Soal Menjodohkan <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0">Kolom Kiri (Items to Match)</label>
                                            <button type="button" id="add-left-item" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Tambah Item
                                            </button>
                                        </div>
                                        <div id="left-items-list">
                                            <!-- Left items will be added here -->
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0">Kolom Kanan (Matching Options)</label>
                                            <button type="button" id="add-right-item" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Tambah Pilihan
                                            </button>
                                        </div>
                                        <div id="right-items-list">
                                            <!-- Right items will be added here -->
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-danger mt-2" id="matching-info">
                                    <small><i class="fas fa-info-circle"></i> Setiap item di kolom kiri harus dipasangkan dengan satu pilihan di kolom kanan</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fas fa-paper-plane"></i> Simpan Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .option-item {
        transition: all 0.3s ease;
        border-left: 4px solid #dee2e6;
    }

    .option-item.has-correct-answer {
        border-left-color: #28a745;
        background-color: #f8fff9;
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .drag-handle {
        cursor: move;
        color: #6c757d;
    }

    .drag-handle:hover {
        color: #495057;
    }

    .option-number {
        font-weight: bold;
        color: #495057;
        min-width: 30px;
        text-align: center;
    }
    .tf-row textarea {
        width: 100%;
        min-height: 60px;
        resize: vertical;
    }
</style>
@endpush

@push('scripts')

<script>
    // Initialize TinyMCE
    let tinymceEditor = null;

    tinymce.init({
        selector: 'textarea[name="question_text"]',
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak code wordcount',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code | preview',
        toolbar_mode: 'scrolling',
        height: 300,
        menubar: false,
        branding: false,
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px }',
        setup: function(editor) {
            tinymceEditor = editor;
            editor.on('change', function() {
                editor.save();
            });
        }
    });

    $(document).ready(function() {
        let optionCounter = 0;
        let isFormSubmitting = false;

        // Initialize form from localStorage or old values
        initializeForm();

        // Form validation and submission
        $('#question-form').on('submit', function(e) {
            e.preventDefault(); // Always prevent default first

            if (isFormSubmitting) {
                return false;
            }

            if (!validateForm()) {
                return false;
            }
            isFormSubmitting = true;
            $('#submit-btn').addClass('loading').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            // Submit the form
            this.submit();
        });

        // Handle question format change
        $('#question_format').on('change', function() {
            const questionFormat = $(this).val();
            updateQuestionFormatDisplay(questionFormat);
            $('#question_format_hidden').val(questionFormat);
        });

        // Handle question type change
        $('#question_type').on('change', function() {
            const questionType = $(this).val();
            updateQuestionTypeDisplay(questionType);
        });

        // Handle option format change
        $('#option_format').on('change', function() {
            const optionFormat = $(this).val();
            $('#option_format_hidden').val(optionFormat);
            regenerateOptions();
        });

        // Add option button click
        $('#add-option').on('click', function() {
            const questionType = $('#question_type').val();
            if (questionType === 'multiple_choice') {
                addOption('radio');
            } else if (questionType === 'true_false') {
                addTFRow(); // tambah baris pernyataan
            } else if (questionType === 'multiple_response') {
                addOption('checkbox');
            } else if (questionType === 'tkp') {
                addOption('score');
            } else if (questionType === 'matching') {
                // For matching, no direct option adding
            }
        });

        // Add TF row
        $('#add-tf').on('click', function(){ addTFRow(); });

        // Remove option handler
        $(document).on('click', '.remove-option', function() {
            const optionsCount = $('.option-item').length;
            const questionType = $('#question_type').val();
            const minOptions = questionType === 'true_false' ? 2 : 2;

            if (optionsCount > minOptions) {
                $(this).closest('.option-item').remove();
                updateOptionNumbers();
                updateCorrectAnswerInfo();
            } else {
                showAlert('error', 'Minimal harus ada 2 pilihan jawaban');
            }
        });

        $(document).on('click', '.remove-tf-row', function() {
            if ($('#tf-list .tf-row').length > 1) {
                $(this).closest('.tf-row').remove();
                refreshTfRowIndexes();
            } else {
                showAlert('error', 'Minimal harus ada satu pernyataan Benar/Salah');
            }
        });

        // Image preview handler
        $('#question_image').on('change', function() {
            handleImagePreview(this, '#image-preview', '#preview-img');
        });

        // Remove image handler
        $('#remove-image').on('click', function() {
            removeImagePreview('#question_image', '#image-preview');
        });

        // Handle correct answer selection change
        $(document).on('change', 'input[name="correct_answer"], input[name="correct_answer[]"]', function() {
            updateCorrectAnswerInfo();
            updateOptionHighlight();
        });

        // Matching question handlers
        $('#add-left-item').on('click', function() {
            addLeftItem();
        });

        $('#add-right-item').on('click', function() {
            addRightItem();
        });

        $(document).on('click', '.remove-left-item', function() {
            if ($('.left-item').length > 2) {
                $(this).closest('.left-item').remove();
                updateMatchingNumbers();
            } else {
                showAlert('error', 'Minimal harus ada 2 item di kolom kiri');
            }
        });

        $(document).on('click', '.remove-right-item', function() {
            if ($('.right-item').length > 2) {
                $(this).closest('.right-item').remove();
                updateMatchingNumbers();
            } else {
                showAlert('error', 'Minimal harus ada 2 pilihan di kolom kanan');
            }
        });

        // Functions
        function initializeForm() {
            // Get subject_id from localStorage
            const subjectId = localStorage.getItem('subject_id');
            if (subjectId && !$('#subject_id').val()) {
                $('#subject_id').val(subjectId);
            }

            // Initialize displays based on current values
            const questionFormat = $('#question_format').val();
            const questionType = $('#question_type').val();

            if (questionFormat) {
                updateQuestionFormatDisplay(questionFormat);
                $('#question_format_hidden').val(questionFormat);
            }

            if (questionType) {
                updateQuestionTypeDisplay(questionType);
            }
        }

        function updateQuestionFormatDisplay(questionFormat) {
            const textContainer = $('#question-text-container');
            const imageContainer = $('#question-image-container');
            const textRequired = $('#text-required');
            const imageRequired = $('#image-required');

            // Reset visibility
            textContainer.hide();
            imageContainer.hide();

            if (questionFormat === 'text') {
                textContainer.show();
                textRequired.show();
                imageRequired.hide();
            } else if (questionFormat === 'image') {
                imageContainer.show();
                textRequired.hide();
                imageRequired.show();
            } else if (questionFormat === 'text_image') {
                textContainer.show();
                imageContainer.show();
                textRequired.show();
                imageRequired.show();
            }
        }

        function updateQuestionTypeDisplay(questionType) {
            const optionsContainer = $('#options-container');
            const addOptionBtn = $('#add-option');
            const infoText = $('#info-text');
            const correctInfo = $('#correct-answer-info');

            optionsContainer.hide();
            $('#truefalse-container').hide();
            $('#matching-container').hide();
            correctInfo.hide();
            addOptionBtn.hide();
            $('#options-instruction').text('Tambahkan pilihan jawaban untuk soal');

            $('#options-list').empty();
            optionCounter = 0;

            if (!questionType) {
                return;
            }

            if (questionType === 'true_false') {
                $('#truefalse-container').show();
                if ($('#tf-list .tf-row').length === 0) {
                    addTFRow();
                    addTFRow();
                }
                return;
            }

            if (questionType === 'matching') {
                $('#left-items-list').empty();
                $('#right-items-list').empty();
                leftItemCounter = 0;
                rightItemCounter = 0;

                for (let i = 0; i < 3; i++) {
                    addLeftItem();
                    addRightItem();
                }

                $('#matching-container').show();
                return;
            }

            optionsContainer.show();
            correctInfo.show();

            if (questionType === 'multiple_choice') {
                for (let i = 0; i < 4; i++) {
                    addOption('radio');
                }
                addOptionBtn.show();
                infoText.text('Pilih salah satu opsi sebagai jawaban yang benar');
            } else if (questionType === 'multiple_response') {
                for (let i = 0; i < 4; i++) {
                    addOption('checkbox');
                }
                addOptionBtn.show();
                infoText.text('Pilih satu atau lebih opsi sebagai jawaban yang benar');
            } else if (questionType === 'tkp') {
                for (let i = 0; i < 5; i++) {
                    addOption('score');
                }
                addOptionBtn.show();
                infoText.text('Masukkan bobot nilai untuk setiap pilihan jawaban (0-100)');
            }

            updateOptionNumbers();
            updateCorrectAnswerInfo();
            updateOptionHighlight();
        }

        function addOption(inputType) {
            optionCounter++;
            const optionFormat = $('#option_format').val() || 'text';
            const optionHtml = generateOptionHtml(inputType, optionCounter, optionFormat);
            $('#options-list').append(optionHtml);
            updateOptionNumbers();
        }

        // Function to add option with predetermined text (for true/false)
        function addOptionWithText(inputType, predefinedText) {
            optionCounter++;
            const optionFormat = $('#option_format').val() || 'text';
            const optionHtml = generateOptionHtmlWithText(inputType, optionCounter, optionFormat, predefinedText);
            $('#options-list').append(optionHtml);
            updateOptionNumbers();
        }

        function generateOptionHtml(inputType, counter, optionFormat) {
            let optionContentHtml = '';
            let checkboxHtml = '';

            if (optionFormat === 'text') {
                optionContentHtml = `
                    <input type="text" name="options[]" class="form-control" placeholder="Masukkan pilihan jawaban ${counter}" required>
                `;
            } else if (optionFormat === 'image') {
                optionContentHtml = `
                    <input type="file" name="option_images[]" class="form-control mb-2" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml" required>
                    <input type="hidden" name="options[]" value="image_${counter}">
                    <small class="form-text text-muted">Format: JPEG, JPG, PNG, GIF, SVG. Maksimal 2MB.</small>
                `;
            } else if (optionFormat === 'text_image') {
                optionContentHtml = `
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="Masukkan pilihan jawaban ${counter}" required>
                    <input type="file" name="option_images[]" class="form-control mb-2" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml" required>
                    <small class="form-text text-muted">Format: JPEG, JPG, PNG, GIF, SVG. Maksimal 2MB.</small>
                `;
            }

            // For TKP questions, add score input instead of checkbox
            if (inputType === 'score') {
                checkboxHtml = `
                    <div class="score-input-wrapper mt-1">
                        <label class="form-label">Bobot Nilai:</label>
                        <input type="number" name="option_scores[]" class="form-control form-control-sm" min="0" max="100" placeholder="0-100" required>
                    </div>
                `;
            } else {
                checkboxHtml = `
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="${inputType}" name="correct_answer${inputType === 'checkbox' ? '[]' : ''}" value="${counter}" id="correct_${counter}">
                        <label class="form-check-label" for="correct_${counter}">
                            ${inputType === 'radio' ? 'Benar' : 'Pilih'}
                        </label>
                    </div>
                `;
            }

            return `
                <div class="option-item mb-3 p-3 border rounded" data-option="${counter}">
                    <div class="row align-items-start">
                        <div class="col-auto">
                            <div class="option-number">${counter}</div>
                        </div>
                        <div class="col-auto">
                            ${checkboxHtml}
                        </div>
                        <div class="col">
                            ${optionContentHtml}
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-option" data-option="${counter}" title="Hapus pilihan">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Function to generate option HTML with predetermined text
        function generateOptionHtmlWithText(inputType, counter, optionFormat, predefinedText) {
            let optionContentHtml = '';
            let checkboxHtml = '';

            if (optionFormat === 'image') {
                optionContentHtml = `
                    <input type="hidden" name="options[]" value="image_${counter}">
                    <div class="mb-2 font-weight-bold">${predefinedText}</div>
                    <input type="file" name="option_images[]" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml" required>
                    <small class="form-text text-muted">Format: JPEG, JPG, PNG, GIF, SVG. Maksimal 2MB.</small>
                `;
            } else if (optionFormat === 'text_image') {
                optionContentHtml = `
                    <input type="text" name="options[]" class="form-control mb-2" value="${predefinedText}" readonly required>
                    <input type="file" name="option_images[]" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml" required>
                    <small class="form-text text-muted">Format: JPEG, JPG, PNG, GIF, SVG. Maksimal 2MB.</small>
                `;
            } else {
                optionContentHtml = `
                    <input type="text" name="options[]" class="form-control" value="${predefinedText}" readonly required>
                `;
            }

            checkboxHtml = `
                <div class="form-check mt-1">
                    <input class="form-check-input" type="${inputType}" name="correct_answer${inputType === 'checkbox' ? '[]' : ''}" value="${counter}" id="correct_${counter}">
                    <label class="form-check-label" for="correct_${counter}">
                        ${inputType === 'radio' ? 'Benar' : 'Pilih'}
                    </label>
                </div>
            `;

            return `
                <div class="option-item mb-3 p-3 border rounded" data-option="${counter}">
                    <div class="row align-items-start">
                        <div class="col-auto">
                            <div class="option-number">${counter}</div>
                        </div>
                        <div class="col-auto">
                            ${checkboxHtml}
                        </div>
                        <div class="col">
                            ${optionContentHtml}
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-option" data-option="${counter}" title="Hapus pilihan" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function regenerateOptions() {
            const questionType = $('#question_type').val();
            if (questionType) {
                updateQuestionTypeDisplay(questionType);
            }
        }

        function updateOptionNumbers() {
            $('.option-item').each(function(index) {
                $(this).find('.option-number').text(index + 1);
                $(this).attr('data-option', index + 1);

                // Update input values and IDs
                const newValue = index + 1;
                $(this).find('input[name="correct_answer"], input[name="correct_answer[]"]').val(newValue).attr('id', `correct_${newValue}`);
                $(this).find('label.form-check-label').attr('for', `correct_${newValue}`);
                $(this).find('.remove-option').attr('data-option', newValue);

                const hiddenPlaceholder = $(this).find('input[type="hidden"][name="options[]"]');
                if (hiddenPlaceholder.length && hiddenPlaceholder.val().toString().startsWith('image_')) {
                    hiddenPlaceholder.val(`image_${newValue}`);
                }
            });
        }

        function updateCorrectAnswerInfo() {
            const checkedAnswers = $('input[name="correct_answer"]:checked, input[name="correct_answer[]"]:checked').length;
            const questionType = $('#question_type').val();

            if (questionType === 'multiple_response') {
                $('#info-text').html(`<span class="text-${checkedAnswers > 0 ? 'success' : 'warning'}">${checkedAnswers} jawaban dipilih</span>`);
            } else {
                $('#info-text').html(`<span class="text-${checkedAnswers > 0 ? 'success' : 'warning'}">${checkedAnswers > 0 ? 'Jawaban sudah dipilih' : 'Belum ada jawaban yang dipilih'}</span>`);
            }
        }

        function updateOptionHighlight() {
            $('.option-item').removeClass('has-correct-answer');
            $('input[name="correct_answer"]:checked, input[name="correct_answer[]"]:checked').closest('.option-item').addClass('has-correct-answer');
        }

        function handleImagePreview(input, previewContainer, previewImg) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const file = input.files[0];

                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('error', 'Ukuran file terlalu besar. Maksimal 2MB.');
                    input.value = '';
                    return;
                }

                reader.onload = function(e) {
                    $(previewImg).attr('src', e.target.result);
                    $(previewContainer).show();
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImagePreview(inputSelector, previewContainer) {
            $(inputSelector).val('');
            $(previewContainer).hide();
        }

        function validateForm() {
            let isValid = true;
            const errors = [];

            // Validate question format and content
            const questionFormat = $('#question_format').val();
            if (!questionFormat) {
                errors.push('Format soal harus dipilih');
                isValid = false;
            } else {
                if ((questionFormat === 'text' || questionFormat === 'text_image') &&
                    tinymceEditor && tinymceEditor.getContent().trim() === '') {
                    errors.push('Pertanyaan harus diisi');
                    isValid = false;
                }

                if ((questionFormat === 'image' || questionFormat === 'text_image') &&
                    !$('#question_image').val()) {
                    errors.push('Gambar soal harus dipilih');
                    isValid = false;
                }
            }

            // Validate correct answers or scores
            const questionType = $('#question_type').val();

            if (questionType === 'tkp') {
                // Validate TKP scores
                const scoreInputs = $('input[name="option_scores[]"]');
                let validScores = 0;

                scoreInputs.each(function() {
                    const value = parseInt($(this).val());
                    if (!isNaN(value) && value >= 0 && value <= 100) {
                        validScores++;
                    }
                });

                if (validScores === 0) {
                    errors.push('Harus mengisi bobot nilai untuk semua pilihan jawaban (0-100)');
                    isValid = false;
                }
            } else if (questionType === 'matching') {
                // Validate matching questions
                const leftItems = $('input[name="left_items[]"]');
                const rightItems = $('input[name="right_items[]"]');
                const matches = $('select[name="matches[]"]');


                if (leftItems.length < 2) {
                    errors.push('Harus ada minimal 2 item di kolom kiri');
                    isValid = false;
                }

                if (rightItems.length < 2) {
                    errors.push('Harus ada minimal 2 pilihan di kolom kanan');
                    isValid = false;
                }

                // Check if all left items have text
                let leftItemsValid = true;
                leftItems.each(function() {
                    if ($(this).val().trim() === '') {
                        leftItemsValid = false;
                        return false;
                    }
                });

                if (!leftItemsValid) {
                    errors.push('Semua item di kolom kiri harus diisi');
                    isValid = false;
                }

                // Check if all right items have text
                let rightItemsValid = true;
                rightItems.each(function() {
                    if ($(this).val().trim() === '') {
                        rightItemsValid = false;
                        return false;
                    }
                });

                if (!rightItemsValid) {
                    errors.push('Semua pilihan di kolom kanan harus diisi');
                    isValid = false;
                }

                // Check if all matches are selected
                let matchesValid = true;
                matches.each(function() {
                    if ($(this).val() === '' || $(this).val() === null) {
                        matchesValid = false;
                        return false;
                    }
                });

                if (!matchesValid) {
                    errors.push('Setiap item di kolom kiri harus dipasangkan dengan pilihan di kolom kanan');
                    isValid = false;
                }

            } else if (questionType === 'true_false' && $('#truefalse-container').is(':visible')) {
                // Validate True/False grid statements
                const rows = $('#tf-list .tf-row');
                if (rows.length < 1) {
                    errors.push('Minimal 1 pernyataan untuk soal True/False');
                    isValid = false;
                }
                rows.each(function(){
                    const text = $(this).find('textarea[name="tf_statements[]"]').val().trim();
                    const fileInput = $(this).find('input[name="tf_statement_images[]"]')[0];
                    const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                    const chosen = $(this).find('input[type=radio]:checked').length;

                    if (text === '' && !hasFile) {
                        errors.push('Setiap pernyataan harus memiliki teks atau gambar');
                        isValid = false;
                        return false;
                    }
                    if (chosen === 0) {
                        errors.push('Tentukan Benar/Salah untuk setiap pernyataan');
                        isValid = false;
                        return false;
                    }
                });
            } else {
                // Validate correct answers for other question types
                const correctAnswers = $('input[name="correct_answer"]:checked, input[name="correct_answer[]"]:checked').length;

                if (correctAnswers === 0) {
                    errors.push('Harus memilih jawaban yang benar');
                    isValid = false;
                }
            }

            // Validate options content (skip for matching questions)
            if (questionType !== 'matching') {
                const textOptions = $('input[name="options[]"]').filter(function() {
                    return $(this).is(':visible') && $(this).val().trim() === '';
                });

                if (textOptions.length > 0) {
                    errors.push('Semua pilihan jawaban harus diisi');
                    isValid = false;
                }
            }

            if (!isValid) {
                showAlert('error', 'Terdapat kesalahan dalam pengisian form:<br>• ' + errors.join('<br>• '));
            }

            return isValid;
        }

        // Matching question functions
        let leftItemCounter = 0;
        let rightItemCounter = 0;
        // True/False grid helpers
        function addTFRow() {
            const row = `
                <tr class="tf-row">
                    <td>
                        <textarea name="tf_statements[]" class="form-control" placeholder="Tulis pernyataan" rows="2"></textarea>
                        <div class="mt-2">
                            <input type="file" name="tf_statement_images[]" class="form-control tf-statement-image" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml">
                            <small class="text-muted">Opsional: unggah gambar untuk menggantikan teks.</small>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <input type="radio" value="true">
                    </td>
                    <td class="text-center align-middle">
                        <input type="radio" value="false">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-tf-row" title="Hapus pernyataan">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;

            $('#tf-list').append(row);
            refreshTfRowIndexes();
        }

        function refreshTfRowIndexes() {
            $('#tf-list .tf-row').each(function(index) {
                $(this).find('input[type="radio"]').attr('name', `tf_correct[${index}]`);
            });
        }

        function addLeftItem() {
            leftItemCounter++;
            const leftItemHtml = `
                <div class="left-item mb-2 p-2 border rounded">
                    <div class="d-flex align-items-center">
                        <div class="item-number mr-2">${leftItemCounter}.</div>
                        <input type="text" name="left_items[]" class="form-control mr-2" placeholder="Item yang akan dijodohkan" required>
                        <select name="matches[]" class="form-control mr-2" style="max-width: 150px;" required>
                            <option value="">Pilih pasangan</option>
                        </select>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-left-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#left-items-list').append(leftItemHtml);
            updateMatchingNumbers();
        }

        function addRightItem() {
            rightItemCounter++;
            const rightItemHtml = `
                <div class="right-item mb-2 p-2 border rounded">
                    <div class="d-flex align-items-center">
                        <div class="item-number mr-2">${String.fromCharCode(64 + rightItemCounter)}</div>
                        <input type="text" name="right_items[]" class="form-control mr-2" placeholder="Pilihan untuk dijodohkan" required>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-right-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#right-items-list').append(rightItemHtml);
            updateMatchingNumbers();
        }

        function updateMatchingNumbers() {
            // Update left item numbers
            $('.left-item').each(function(index) {
                $(this).find('.item-number').text((index + 1) + '.');
            });

            // Update right item numbers and populate select options
            const rightOptions = [];
            $('.right-item').each(function(index) {
                const letter = String.fromCharCode(65 + index); // A, B, C, etc.
                $(this).find('.item-number').text(letter);
                rightOptions.push({value: index, label: letter});
            });

            // Update all select dropdowns in left items
            $('.left-item select').each(function() {
                const currentValue = $(this).val();
                $(this).empty().append('<option value="">Pilih pasangan</option>');

                rightOptions.forEach(option => {
                    const selected = currentValue == option.value ? 'selected' : '';
                    $(this).append(`<option value="${option.value}" ${selected}>${option.label}</option>`);
                });
            });
        }

        function showAlert(type, message) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            $('.card-body').prepend(alertHtml);
            $('html, body').animate({ scrollTop: 0 }, 300);
        }

        // Global function for reset button
        window.resetForm = function() {
            if (confirm('Apakah Anda yakin ingin mereset form? Semua data yang telah diisi akan hilang.')) {
                $('#question-form')[0].reset();
                if (tinymceEditor) {
                    tinymceEditor.setContent('');
                }
                $('#options-list').empty();
                $('#left-items-list').empty();
                $('#right-items-list').empty();
                $('#options-container').hide();
                $('#matching-container').hide();
                $('#question-text-container').show();
                $('#question-image-container').hide();
                $('#image-preview').hide();
                optionCounter = 0;
                $('.alert').remove();
            }
        };
    });
</script>
@endpush
