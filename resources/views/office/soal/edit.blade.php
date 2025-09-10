@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-question"></i> Edit Soal</h4>
                <div class="ml-auto">
                    <a href="javascript:history.back()" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            {{-- Container untuk notifikasi --}}
            <div id="alert-container"></div>
            <div class="card-body mb-0">
                <form action="{{ route('soal.update', $soal->id) }}" method="post" enctype="multipart/form-data" id="question-form" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="subject_id" value="{{ $soal->subject_id }}">

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="question_category">Kategori Soal <span class="text-danger">*</span></label>
                            <select name="question_category" id="question_category" class="form-control custom-select @error('question_category') is-invalid @enderror" required>
                                <option value="" disabled>Pilih Kategori</option>
                                <option value="Literasi" {{ old('question_category', $soal->question_category) == 'Literasi' ? 'selected' : '' }}>Literasi</option>
                                <option value="Numerasi" {{ old('question_category', $soal->question_category) == 'Numerasi' ? 'selected' : '' }}>Numerasi</option>
                                <option value="Teknis" {{ old('question_category', $soal->question_category) == 'Teknis' ? 'selected' : '' }}>Teknis</option>
                                <option value="Pedagogik" {{ old('question_category', $soal->question_category) == 'Pedagogik' ? 'selected' : '' }}>Pedagogik</option>
                                <option value="TKP" {{ old('question_category', $soal->question_category) == 'TKP' ? 'selected' : '' }}>TKP (Tes Karakteristik Pribadi)</option>
                            </select>
                            @error('question_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="question_type">Tipe Soal <span class="text-danger">*</span></label>
                            <select name="question_type" id="question_type" class="form-control custom-select @error('question_type') is-invalid @enderror" required>
                                <option value="" disabled>Pilih Tipe</option>
                                <option value="multiple_choice" {{ old('question_type', $soal->question_type) == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda (Satu Jawaban)</option>
                                <option value="multiple_response" {{ old('question_type', $soal->question_type) == 'multiple_response' ? 'selected' : '' }}>Pilihan Ganda (Banyak Jawaban)</option>
                                <option value="true_false" {{ old('question_type', $soal->question_type) == 'true_false' ? 'selected' : '' }}>Benar/Salah</option>
                                <option value="tkp" {{ old('question_type', $soal->question_type) == 'tkp' ? 'selected' : '' }}>TKP (Tes Karakteristik Pribadi)</option>
                                <option value="matching" {{ old('question_type', $soal->question_type) == 'matching' ? 'selected' : '' }}>Matching (Menjodohkan)</option>
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
                                <option value="" disabled>Pilih Format</option>
                                <option value="text" {{ old('question_format', $soal->question_format) == 'text' ? 'selected' : '' }}>Teks</option>
                                <option value="image" {{ old('question_format', $soal->question_format) == 'image' ? 'selected' : '' }}>Gambar</option>
                                <option value="text_image" {{ old('question_format', $soal->question_format) == 'text_image' ? 'selected' : '' }}>Teks + Gambar</option>
                            </select>
                            @error('question_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="option_format">Format Pilihan Jawaban <span class="text-danger">*</span></label>
                            <select name="option_format" id="option_format" class="form-control custom-select @error('option_format') is-invalid @enderror" required>
                                <option value="" disabled>Pilih Format</option>
                                <option value="text" {{ old('option_format', $soal->option_format) == 'text' ? 'selected' : '' }}>Teks</option>
                                <option value="image" {{ old('option_format', $soal->option_format) == 'image' ? 'selected' : '' }}>Gambar</option>
                                <option value="text_image" {{ old('option_format', $soal->option_format) == 'text_image' ? 'selected' : '' }}>Teks + Gambar</option>
                            </select>
                            @error('option_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group" id="question-text-container" style="display: none;">
                        <label for="question_text">Teks Pertanyaan <span class="text-danger" id="text-required">*</span></label>
                        <textarea name="question_text" id="texteditor" class="@error('question_text') is-invalid @enderror">{{ old('question_text', $soal->question_text) }}</textarea>
                        @error('question_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group" id="question-image-container" style="display: none;">
                        <label for="question_image">Gambar Soal <span class="text-danger" id="image-required">*</span></label>
                        <input type="file" name="question_image" id="question_image" class="form-control @error('question_image') is-invalid @enderror" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml">
                        @error('question_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Format yang diizinkan: JPEG, JPG, PNG, GIF, SVG. Maksimal 2MB.</small>
                        
                        @if($soal->question_image)
                            <div class="mt-2 current-image-wrapper">
                                <label class="form-label">Gambar Saat Ini:</label>
                                <div class="current-image">
                                    <img src="{{ asset('storage/' . $soal->question_image) }}" alt="Current Question Image" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                    <div class="mt-1">
                                        <small class="text-muted">Pilih file baru untuk mengganti gambar ini</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div id="image-preview" class="mt-2" style="display: none;">
                            <label class="form-label">Preview Gambar Baru:</label>
                            <div>
                                <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                <button type="button" id="remove-image-btn" class="btn btn-sm btn-danger ml-2">
                                    <i class="fas fa-times"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options Container -->
                    <div id="options-container">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0">Pilihan Jawaban <span class="text-danger">*</span></label>
                            <button type="button" id="add-option-btn" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Tambah Pilihan
                            </button>
                        </div>
                        <div class="alert alert-info" id="correct-answer-info">
                            <small><i class="fas fa-info-circle"></i> <span id="info-text"></span></small>
                        </div>
                        <div id="options-list">
                            <!-- Pilihan jawaban dinamis akan ditambahkan di sini -->
                        </div>
                    </div>

                    <!-- Matching Container -->
                    <div id="matching-container" style="display: none;">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0">Pasangan Soal Menjodohkan <span class="text-danger">*</span></label>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="mb-0">Kolom Kiri (Items to Match)</label>
                                    <button type="button" id="add-left-item-btn" class="btn btn-sm btn-primary">
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
                                    <button type="button" id="add-right-item-btn" class="btn btn-sm btn-success">
                                        <i class="fas fa-plus"></i> Tambah Pilihan
                                    </button>
                                </div>
                                <div id="right-items-list">
                                    <!-- Right items will be added here -->
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-2" id="matching-info">
                            <small><i class="fas fa-info-circle"></i> Setiap item di kolom kiri harus dipasangkan dengan satu pilihan di kolom kanan</small>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fas fa-save"></i> Update Soal
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" id="reset-btn">
                            <i class="fas fa-undo"></i> Reset
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
    .option-item.is-invalid {
        border-left-color: #dc3545;
        background-color: #f8d7da;
    }
    .option-item.has-correct-answer {
        border-left-color: #28a745;
        background-color: #f0fff4;
    }
    .submit-loading {
        opacity: 0.6;
        pointer-events: none;
    }
    .current-image {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        display: inline-block;
    }
    #alert-container .alert {
        border-radius: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.tiny.cloud/1/lgwbcigxg5kj9r7fpvlp83nmg38onp3bntizwoibu78t09r5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- TinyMCE Initialization ---
        let tinymceEditor;
        tinymce.init({
            selector: 'textarea#texteditor',
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak code wordcount',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code | preview',
            toolbar_mode: 'scrolling',
            height: 300,
            menubar: false,
            branding: false,
            setup: editor => {
                tinymceEditor = editor;
                editor.on('change', () => editor.save());
            }
        });

        // --- DOM Element References ---
        const form = document.getElementById('question-form');
        const questionCategory = document.getElementById('question_category');
        const questionType = document.getElementById('question_type');
        const questionFormat = document.getElementById('question_format');
        const optionFormat = document.getElementById('option_format');
        const questionTextContainer = document.getElementById('question-text-container');
        const questionImageContainer = document.getElementById('question-image-container');
        const optionsContainer = document.getElementById('options-container');
        const optionsList = document.getElementById('options-list');
        const addOptionBtn = document.getElementById('add-option-btn');
        const infoText = document.getElementById('info-text');
        const questionImageInput = document.getElementById('question_image');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        const removeImageBtn = document.getElementById('remove-image-btn');
        const submitBtn = document.getElementById('submit-btn');
        const resetBtn = document.getElementById('reset-btn');

        // --- Initial Data ---
        const existingOptions = @json($soal->questionOptions ?? []);
        const correctAnswers = existingOptions.filter(opt => opt.is_correct).map(opt => opt.id.toString());
        
        // --- State ---
        let isSubmitting = false;

        // --- Functions ---

        /** Mengatur tampilan format soal (teks/gambar) */
        const updateQuestionFormatDisplay = () => {
            const format = questionFormat.value;
            questionTextContainer.style.display = (format === 'text' || format === 'text_image') ? 'block' : 'none';
            questionImageContainer.style.display = (format === 'image' || format === 'text_image') ? 'block' : 'none';
        };

        /** Mengatur tampilan berdasarkan tipe soal (pilihan ganda, benar/salah, dll) */
        const handleQuestionTypeChange = () => {
            const type = questionType.value;
            if (type) {
                const isMultipleResponse = type === 'multiple_response';
                const isTrueFalse = type === 'true_false';
                const isTkp = type === 'tkp';
                const isMatching = type === 'matching';

                if (isMatching) {
                    // Hide options container and show matching container
                    optionsContainer.style.display = 'none';
                    document.getElementById('matching-container').style.display = 'block';
                    
                    // Load existing matching data
                    loadMatchingData();
                    return;
                } else {
                    // Show options container and hide matching container
                    optionsContainer.style.display = 'block';
                    document.getElementById('matching-container').style.display = 'none';
                }

                addOptionBtn.style.display = isTrueFalse ? 'none' : 'block';
                
                if (isTkp) {
                    infoText.textContent = 'Masukkan bobot nilai untuk setiap pilihan jawaban (0-100).';
                } else {
                    if (isTrueFalse) {
                        infoText.textContent = 'Pilih salah satu: Benar atau Salah.';
                    } else {
                        infoText.textContent = isMultipleResponse ? 'Pilih satu atau lebih jawaban benar.' : 'Pilih satu jawaban benar.';
                    }
                }
                
                // Hapus pilihan yang ada dan buat ulang
                optionsList.innerHTML = ''; 
                
                if (isTrueFalse) {
                    // Otomatis buat pilihan Benar dan Salah
                    const trueOption = existingOptions.find(o => o.option_text && o.option_text.toLowerCase() === 'benar') || { id: `new_true`, option_text: 'Benar' };
                    const falseOption = existingOptions.find(o => o.option_text && o.option_text.toLowerCase() === 'salah') || { id: `new_false`, option_text: 'Salah' };

                    addOption(trueOption, correctAnswers.includes(trueOption.id.toString()), true);
                    addOption(falseOption, correctAnswers.includes(falseOption.id.toString()), true);
                } else {
                    // Muat pilihan yang sudah ada dari database
                    existingOptions.forEach(opt => addOption(opt, correctAnswers.includes(opt.id.toString())));
                }

            }
            updateOptionNumbers();
            updateCorrectAnswerInfo();
            updateOptionHighlight();
        };

        /**
         * Menambah satu baris pilihan jawaban ke dalam daftar
         * @param {object} optionData - Data pilihan dari database (opsional)
         * @param {boolean} isCorrect - Apakah pilihan ini benar (opsional)
         * @param {boolean} isReadOnly - Apakah teks pilihan tidak bisa diubah (untuk Benar/Salah)
         */
        const addOption = (optionData = {}, isCorrect = false, isReadOnly = false) => {
            const type = questionType.value;
            const format = optionFormat.value;
            const index = optionsList.children.length;
            
            const optionId = optionData.id || `new_${index}`;
            const optionText = optionData.option_text || '';
            const optionImage = optionData.option_image || null;
            const optionScore = optionData.score || 0;

            let checkboxHtml = '';
            
            if (type === 'tkp') {
                // For TKP questions, show score input instead of correct/incorrect checkbox
                checkboxHtml = `
                    <div class="score-input-wrapper pt-1 mr-3">
                        <label class="form-label small">Bobot:</label>
                        <input type="number" name="option_scores[]" class="form-control form-control-sm" min="0" max="100" placeholder="0-100" value="${optionScore}" required>
                    </div>
                `;
            } else {
                const inputType = (type === 'multiple_choice' || type === 'true_false') ? 'radio' : 'checkbox';
                const name = `correct_answer${inputType === 'checkbox' ? '[]' : ''}`;
                
                checkboxHtml = `
                    <div class="form-check pt-1 mr-3">
                        <input class="form-check-input correct-answer-input" type="${inputType}" name="${name}" value="${optionId}" ${isCorrect ? 'checked' : ''}>
                        <label class="form-check-label">Benar</label>
                    </div>
                `;
            }

            let contentHtml = '';
            if (format === 'text' || format === 'text_image') {
                contentHtml += `<input type="text" name="options[${optionId}][text]" class="form-control mb-2" placeholder="Teks Pilihan Jawaban" value="${optionText}" ${isReadOnly ? 'readonly' : ''} required>`;
            }
            if (format === 'image' || format === 'text_image') {
                contentHtml += `<input type="file" name="options[${optionId}][image]" class="form-control option-image-input" accept="image/jpeg,image/jpg,image/png,image/gif,image/svg+xml">`;
                if(optionImage) {
                    contentHtml += `
                        <div class="mt-2 current-image">
                            <img src="/storage/${optionImage}" class="img-thumbnail" style="max-width: 150px;">
                            <small class="d-block text-muted">Ganti dengan file baru di atas</small>
                        </div>`;
                }
            }

            const newOption = document.createElement('div');
            newOption.className = 'option-item mb-3 p-3 border rounded';
            newOption.dataset.id = optionId;
            newOption.innerHTML = `
                <div class="d-flex align-items-start">
                    <div class="option-number font-weight-bold mr-3 pt-1">${index + 1}</div>
                    ${checkboxHtml}
                    <div class="flex-grow-1">${contentHtml}</div>
                    <button type="button" class="btn btn-outline-danger btn-sm ml-3 remove-option-btn" title="Hapus Pilihan">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            optionsList.appendChild(newOption);
        };

        /** Mengubah format pilihan jawaban (teks/gambar) tanpa kehilangan data yang sudah diinput */
        const transformOptions = () => {
            const currentOptions = [];
            // Simpan data yang ada di DOM
            optionsList.querySelectorAll('.option-item').forEach(item => {
                const textInput = item.querySelector('input[type="text"]');
                currentOptions.push({
                    id: item.dataset.id,
                    text: textInput ? textInput.value : '',
                    isCorrect: item.querySelector('.correct-answer-input').checked,
                    // Note: image file cannot be preserved, but existing image from server can
                    image: existingOptions.find(o => o.id.toString() === item.dataset.id)?.option_image || null
                });
            });

            optionsList.innerHTML = ''; // Kosongkan daftar
            // Buat ulang dengan format baru
            currentOptions.forEach(optData => addOption({id: optData.id, option_text: optData.text, option_image: optData.image}, optData.isCorrect));
            
            updateOptionNumbers();
            updateOptionHighlight();
        };


        /** Memperbarui nomor urut pilihan jawaban */
        const updateOptionNumbers = () => {
            optionsList.querySelectorAll('.option-item').forEach((item, index) => {
                item.querySelector('.option-number').textContent = index + 1;
                // Sembunyikan tombol hapus jika tipe soal Benar/Salah
                const removeBtn = item.querySelector('.remove-option-btn');
                if(removeBtn) {
                    removeBtn.style.display = questionType.value === 'true_false' ? 'none' : 'block';
                }
            });
        };

        /** Memperbarui info jumlah jawaban benar yang dipilih */
        const updateCorrectAnswerInfo = () => {
            const type = questionType.value;
            const infoContainer = document.getElementById('correct-answer-info');
            
            if (type === 'tkp') {
                // For TKP questions, show score summary
                const scoreInputs = optionsList.querySelectorAll('input[name="option_scores[]"]');
                let validScores = 0;
                let totalScore = 0;
                
                scoreInputs.forEach(input => {
                    const value = parseInt(input.value);
                    if (!isNaN(value) && value >= 0 && value <= 100) {
                        validScores++;
                        totalScore += value;
                    }
                });
                
                if (validScores > 0) {
                    infoContainer.classList.remove('alert-info');
                    infoContainer.classList.add('alert-success');
                    infoText.innerHTML = `<strong>${validScores}</strong> pilihan memiliki bobot nilai (Total: ${totalScore}).`;
                } else {
                    infoContainer.classList.remove('alert-success');
                    infoContainer.classList.add('alert-info');
                    infoText.textContent = 'Belum ada bobot nilai yang diisi.';
                }
            } else {
                // For other question types, show correct answer count
                const checkedCount = optionsList.querySelectorAll('.correct-answer-input:checked').length;
                if (checkedCount > 0) {
                    infoContainer.classList.remove('alert-info');
                    infoContainer.classList.add('alert-success');
                    infoText.innerHTML = `<strong>${checkedCount}</strong> jawaban benar telah dipilih.`;
                } else {
                    infoContainer.classList.remove('alert-success');
                    infoContainer.classList.add('alert-info');
                    infoText.textContent = 'Belum ada jawaban benar yang dipilih.';
                }
            }
        };

        /** Menyorot pilihan jawaban yang ditandai sebagai benar */
        const updateOptionHighlight = () => {
            optionsList.querySelectorAll('.option-item').forEach(item => {
                item.classList.toggle('has-correct-answer', item.querySelector('.correct-answer-input').checked);
            });
        };

        /** Menampilkan preview gambar yang diunggah */
        const handleImagePreview = (input, previewContainer, imgElement) => {
            const file = input.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) { // Maks 2MB
                    showAlert('Ukuran file terlalu besar. Maksimal 2MB.', 'danger');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    imgElement.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        };
        
        /** Menghapus preview gambar */
        const removeImagePreview = (input, previewContainer) => {
            input.value = '';
            previewContainer.style.display = 'none';
        };
        
        /** Menampilkan notifikasi */
        const showAlert = (message, type = 'danger') => {
            const alertContainer = document.getElementById('alert-container');
            const alert = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            alertContainer.innerHTML = alert;
            window.scrollTo(0, 0);
        };
        
        /** Validasi form sebelum submit */
        const validateForm = () => {
            let isValid = true;
            let errors = [];
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            // Validasi Kategori, Tipe, Format
            [questionCategory, questionType, questionFormat, optionFormat].forEach(el => {
                if (!el.value) {
                    el.classList.add('is-invalid');
                    errors.push(`'${el.previousElementSibling.innerText.replace(' *', '')}' harus dipilih.`);
                    isValid = false;
                }
            });

            // Validasi Konten Soal
            const qFormat = questionFormat.value;
            if ((qFormat === 'text' || qFormat === 'text_image') && tinymceEditor && tinymceEditor.getContent({ format: 'text' }).trim() === '') {
                errors.push('Teks pertanyaan tidak boleh kosong.');
                isValid = false;
            }
            if (qFormat === 'image' && !questionImageInput.files[0] && !document.querySelector('.current-image-wrapper')) {
                 errors.push('Gambar soal harus diunggah.');
                 questionImageInput.classList.add('is-invalid');
                 isValid = false;
            }
            if (qFormat === 'text_image' && !questionImageInput.files[0] && !document.querySelector('.current-image-wrapper')) {
                 errors.push('Gambar soal harus diunggah untuk format teks + gambar.');
                 questionImageInput.classList.add('is-invalid');
                 isValid = false;
            }

            // Validasi Pilihan Jawaban
            if (optionsList.children.length < 2) {
                errors.push('Minimal harus ada 2 pilihan jawaban.');
                isValid = false;
            }
            
            optionsList.querySelectorAll('.option-item').forEach(item => {
                const textInput = item.querySelector('input[type="text"]');
                const imageInput = item.querySelector('.option-image-input');
                let itemValid = true;

                if (textInput && textInput.required && !textInput.value.trim()) itemValid = false;
                
                // Cek apakah gambar diperlukan
                const hasExistingImage = item.querySelector('.current-image');
                if (imageInput && !imageInput.files[0] && !hasExistingImage) {
                    const oFormat = optionFormat.value;
                    if(oFormat === 'image' || oFormat === 'text_image') itemValid = false;
                }
                
                if(!itemValid){
                    item.classList.add('is-invalid');
                    isValid = false;
                }
            });
            if (!isValid && !errors.includes('Setiap pilihan jawaban harus diisi.')) errors.push('Setiap pilihan jawaban harus diisi.');


            // Validasi Jawaban Benar atau Bobot TKP atau Matching
            const qType = questionType.value;
            if (qType === 'tkp') {
                // Validate TKP scores
                const scoreInputs = optionsList.querySelectorAll('input[name="option_scores[]"]');
                let hasValidScore = false;
                
                scoreInputs.forEach(input => {
                    const value = parseInt(input.value);
                    if (!isNaN(value) && value >= 0 && value <= 100) {
                        hasValidScore = true;
                    }
                });
                
                if (!hasValidScore) {
                    errors.push('Anda harus mengisi bobot nilai untuk setidaknya satu pilihan jawaban (0-100).');
                    document.getElementById('correct-answer-info').classList.add('is-invalid');
                    isValid = false;
                }
            } else if (qType === 'matching') {
                // Validate matching questions
                const leftItems = document.querySelectorAll('input[name="left_items[]"]');
                const rightItems = document.querySelectorAll('input[name="right_items[]"]');
                const matches = document.querySelectorAll('select[name="matches[]"]');
                
                if (leftItems.length < 2) {
                    errors.push('Harus ada minimal 2 item di kolom kiri.');
                    isValid = false;
                }
                
                if (rightItems.length < 2) {
                    errors.push('Harus ada minimal 2 pilihan di kolom kanan.');
                    isValid = false;
                }
                
                // Check if all left items have text
                leftItems.forEach(input => {
                    if (!input.value.trim()) {
                        errors.push('Semua item di kolom kiri harus diisi.');
                        isValid = false;
                    }
                });
                
                // Check if all right items have text
                rightItems.forEach(input => {
                    if (!input.value.trim()) {
                        errors.push('Semua pilihan di kolom kanan harus diisi.');
                        isValid = false;
                    }
                });
                
                // Check if all matches are selected
                matches.forEach(select => {
                    if (!select.value) {
                        errors.push('Setiap item di kolom kiri harus dipasangkan dengan pilihan di kolom kanan.');
                        isValid = false;
                    }
                });
            } else {
                // Validate correct answers for other question types
                if (optionsList.querySelectorAll('.correct-answer-input:checked').length === 0) {
                    errors.push('Anda harus memilih setidaknya satu jawaban yang benar.');
                    document.getElementById('correct-answer-info').classList.add('is-invalid');
                    isValid = false;
                }
            }

            if (!isValid) {
                showAlert('<strong>Validasi Gagal!</strong><br><ul><li>' + errors.join('</li><li>') + '</li></ul>');
            }
            
            return isValid;
        };

        // --- Event Listeners ---
        questionFormat.addEventListener('change', updateQuestionFormatDisplay);
        questionType.addEventListener('change', handleQuestionTypeChange);
        optionFormat.addEventListener('change', transformOptions);
        
        addOptionBtn.addEventListener('click', () => addOption());

        questionImageInput.addEventListener('change', () => handleImagePreview(questionImageInput, imagePreview, previewImg));
        removeImageBtn.addEventListener('click', () => removeImagePreview(questionImageInput, imagePreview));

        resetBtn.addEventListener('click', () => {
            if (confirm('Apakah Anda yakin ingin mereset semua perubahan?')) {
                window.location.reload();
            }
        });

        // Event delegation untuk tombol/input dinamis
        optionsList.addEventListener('click', e => {
            if (e.target.closest('.remove-option-btn')) {
                if (optionsList.children.length > 2) {
                    e.target.closest('.option-item').remove();
                    updateOptionNumbers();
                    updateCorrectAnswerInfo();
                } else {
                    showAlert('Minimal harus ada 2 pilihan jawaban.', 'warning');
                }
            }
        });

        // Matching question handlers
        document.getElementById('add-left-item-btn').addEventListener('click', () => addMatchingLeftItem());
        document.getElementById('add-right-item-btn').addEventListener('click', () => addMatchingRightItem());

        document.addEventListener('click', e => {
            if (e.target.closest('.remove-left-item-btn')) {
                if (document.querySelectorAll('.left-item').length > 2) {
                    e.target.closest('.left-item').remove();
                    updateMatchingNumbers();
                } else {
                    showAlert('Minimal harus ada 2 item di kolom kiri.', 'warning');
                }
            }
            
            if (e.target.closest('.remove-right-item-btn')) {
                if (document.querySelectorAll('.right-item').length > 2) {
                    e.target.closest('.right-item').remove();
                    updateMatchingNumbers();
                } else {
                    showAlert('Minimal harus ada 2 pilihan di kolom kanan.', 'warning');
                }
            }
        });

        optionsList.addEventListener('change', e => {
            if (e.target.classList.contains('correct-answer-input') || e.target.name === 'option_scores[]') {
                updateCorrectAnswerInfo();
                updateOptionHighlight();
            }
        });
        
        form.addEventListener('submit', e => {
            e.preventDefault();
            if (isSubmitting) return;

            if (validateForm()) {
                isSubmitting = true;
                submitBtn.classList.add('submit-loading');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
                form.submit();
            }
        });

        // --- Matching Functions ---
        let leftItemCounter = 0;
        let rightItemCounter = 0;

        const loadMatchingData = () => {
            const leftItemsList = document.getElementById('left-items-list');
            const rightItemsList = document.getElementById('right-items-list');
            
            // Clear existing items
            leftItemsList.innerHTML = '';
            rightItemsList.innerHTML = '';
            
            // Filter left and right items from existing options
            const leftItems = existingOptions.filter(opt => opt.option_label && opt.option_label.startsWith('L'));
            const rightItems = existingOptions.filter(opt => opt.option_label && opt.option_label.startsWith('R'));
            
            // Load left items
            leftItems.forEach(item => {
                const matchKey = item.option_key; // This should contain which R item it matches (e.g., 'R1', 'R2')
                const matchIndex = matchKey ? parseInt(matchKey.substring(1)) - 1 : null;
                addMatchingLeftItem(item.option_text, matchIndex);
            });
            
            // Load right items  
            rightItems.forEach(item => {
                addMatchingRightItem(item.option_text);
            });
            
            // If no items exist, create defaults
            if (leftItems.length === 0 && rightItems.length === 0) {
                for (let i = 0; i < 2; i++) {
                    addMatchingLeftItem();
                    addMatchingRightItem();
                }
            }
            
            updateMatchingNumbers();
        };

        const addMatchingLeftItem = (text = '', matchIndex = null) => {
            leftItemCounter++;
            const leftItemDiv = document.createElement('div');
            leftItemDiv.className = 'left-item mb-2 p-2 border rounded';
            leftItemDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="item-number mr-2">${leftItemCounter}.</div>
                    <input type="text" name="left_items[]" class="form-control mr-2" placeholder="Item yang akan dijodohkan" value="${text}" required>
                    <select name="matches[]" class="form-control mr-2" style="max-width: 150px;" required>
                        <option value="">Pilih pasangan</option>
                    </select>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-left-item-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            document.getElementById('left-items-list').appendChild(leftItemDiv);
            
            // Set the selected match if provided
            if (matchIndex !== null) {
                const select = leftItemDiv.querySelector('select');
                select.setAttribute('data-match-index', matchIndex);
            }
        };

        const addMatchingRightItem = (text = '') => {
            rightItemCounter++;
            const rightItemDiv = document.createElement('div');
            rightItemDiv.className = 'right-item mb-2 p-2 border rounded';
            rightItemDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="item-number mr-2">${String.fromCharCode(64 + rightItemCounter)}</div>
                    <input type="text" name="right_items[]" class="form-control mr-2" placeholder="Pilihan untuk dijodohkan" value="${text}" required>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-right-item-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            document.getElementById('right-items-list').appendChild(rightItemDiv);
        };

        const updateMatchingNumbers = () => {
            // Update left item numbers
            document.querySelectorAll('.left-item').forEach((item, index) => {
                item.querySelector('.item-number').textContent = (index + 1) + '.';
            });

            // Update right item numbers and populate select options
            const rightOptions = [];
            document.querySelectorAll('.right-item').forEach((item, index) => {
                const letter = String.fromCharCode(65 + index); // A, B, C, etc.
                item.querySelector('.item-number').textContent = letter;
                rightOptions.push({value: index, label: letter});
            });

            // Update all select dropdowns in left items
            document.querySelectorAll('.left-item select').forEach(select => {
                const currentValue = select.value || select.getAttribute('data-match-index');
                select.innerHTML = '<option value="">Pilih pasangan</option>';
                
                rightOptions.forEach(option => {
                    const selected = currentValue == option.value ? 'selected' : '';
                    select.innerHTML += `<option value="${option.value}" ${selected}>${option.label}</option>`;
                });
                
                // Remove the data attribute after setting
                select.removeAttribute('data-match-index');
            });
        };

        // --- Initial Execution ---
        updateQuestionFormatDisplay();
        handleQuestionTypeChange();
    });
</script>
@endpush
