@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-list"></i> Edit Sesi Ujian</h4>
                <div class="ml-auto">
                    <a href="{{ route('disdik.sesi-ujian.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('disdik.sesi-ujian.update', $sesi->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="exam_id">Nama Ujian <span class="text-danger">*</span></label>
                            <select name="exam_id" id="exam_id" class="form-control custom-select @error('exam_id') is-invalid @enderror" required>
                                <option value="">Pilih Ujian</option>
                                @foreach($exam as $examItem)
                                    <option value="{{ $examItem->id }}" {{ (old('exam_id') ?? $sesi->exam_id) == $examItem->id ? 'selected' : '' }}>{{ $examItem->exam_name }}</option>
                                @endforeach
                            </select>
                            @error('exam_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="session_number">Sesi <span class="text-danger">*</span></label>
                            <input type="number" name="session_number" id="session_number" class="form-control @error('session_number') is-invalid @enderror" value="{{ old('session_number') ?? $sesi->session_number }}" placeholder="Masukkan nomor sesi" required>
                            @error('session_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Konfigurasi Mata Pelajaran</label>
                        <div id="subjects-container">
                            @php
                                $sessionSubjects = $sesi->session_subjects ?? [['subject_id' => $sesi->subject_id, 'question_count' => $sesi->question_count ?? 0]];
                            @endphp
                            @foreach($sessionSubjects as $index => $subjectConfig)
                            <div class="subject-row mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label>Mata Pelajaran</label>
                                        <select name="subjects[{{ $index }}][subject_id]" class="form-control custom-select subject-select" required>
                                            <option value="">Pilih Mata Pelajaran</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ $subjectConfig['subject_id'] == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Jumlah Soal</label>
                                        <input type="number" name="subjects[{{ $index }}][question_count]" class="form-control question-count" placeholder="Jumlah soal" min="1" value="{{ $subjectConfig['question_count'] }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Durasi (menit)</label>
                                        <input type="number" name="subjects[{{ $index }}][duration]" class="form-control subject-duration" placeholder="Durasi" min="1" value="{{ $subjectConfig['duration'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-subject" style="display: {{ count($sessionSubjects) > 1 ? 'inline-block' : 'none' }};"><i class="fas fa-trash"></i></button>
                                        <button type="button" class="btn btn-success add-subject ml-2"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="break_duration">Jeda Antar Mapel (menit)</label>
                            <input type="number" name="break_duration" id="break_duration" class="form-control" value="{{ old('break_duration', $sesi->break_duration ?? 1) }}" placeholder="Jeda dalam menit" min="0">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="start_time">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="session_start_time" id="session_start_time" class="form-control @error('session_start_time') is-invalid @enderror" value="{{ old('session_start_time') ?? date('Y-m-d\TH:i', strtotime($sesi->session_start_time)) }}" required>
                            @error('session_start_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_time">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="session_end_time" id="session_end_time" class="form-control @error('session_end_time') is-invalid @enderror" value="{{ old('session_end_time') ?? date('Y-m-d\TH:i', strtotime($sesi->session_end_time)) }}" required>
                            @error('session_end_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="random_question">Random Question <span class="text-danger">*</span></label>
                            <select name="random_question" id="random_question" class="form-control custom-select @error('random_question') is-invalid @enderror" required>
                                <option value="Y" {{ (old('random_question') ?? $sesi->random_question) == 'Y' ? 'selected' : '' }}>Ya</option>
                                <option value="N" {{ (old('random_question') ?? $sesi->random_question) == 'N' ? 'selected' : '' }}>Tidak</option>
                            </select>
                            @error('random_question')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="random_answer">Random Answer <span class="text-danger">*</span></label>
                            <select name="random_answer" id="random_answer" class="form-control custom-select @error('random_answer') is-invalid @enderror" required>
                                <option value="Y" {{ (old('random_answer') ?? $sesi->random_answer) == 'Y' ? 'selected' : '' }}>Ya</option>
                                <option value="N" {{ (old('random_answer') ?? $sesi->random_answer) == 'N' ? 'selected' : '' }}>Tidak</option>
                            </select>
                            @error('random_answer')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="show_result">Show Result <span class="text-danger">*</span></label>
                            <select name="show_result" id="show_result" class="form-control custom-select @error('show_result') is-invalid @enderror" required>
                                <option value="Y" {{ (old('show_result') ?? $sesi->show_result) == 'Y' ? 'selected' : '' }}>Ya</option>
                                <option value="N" {{ (old('show_result') ?? $sesi->show_result) == 'N' ? 'selected' : '' }}>Tidak</option>
                            </select>
                            @error('show_result')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="show_score">Show Score <span class="text-danger">*</span></label>
                            <select name="show_score" id="show_score" class="form-control custom-select @error('show_score') is-invalid @enderror" required>
                                <option value="Y" {{ (old('show_score') ?? $sesi->show_score) == 'Y' ? 'selected' : '' }}>Ya</option>
                                <option value="N" {{ (old('show_score') ?? $sesi->show_score) == 'N' ? 'selected' : '' }}>Tidak</option>
                            </select>
                            @error('show_score')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="session_status">Status <span class="text-danger">*</span></label>
                            <select name="session_status" id="session_status" class="form-control custom-select @error('session_status') is-invalid @enderror" required>
                                <option value="active" {{ (old('session_status') ?? $sesi->session_status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ (old('session_status') ?? $sesi->session_status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('session_status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let subjectIndex = {{ count($sessionSubjects) }};

        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('add-subject') || e.target.closest('.add-subject')) {
                    addSubjectRow();
                }

                if (e.target.classList.contains('remove-subject') || e.target.closest('.remove-subject')) {
                    removeSubjectRow(e.target.closest('.subject-row'));
                }
            });

            function addSubjectRow() {
                const container = document.getElementById('subjects-container');
                const newRow = createSubjectRow(subjectIndex);
                container.appendChild(newRow);
                subjectIndex++;

                // Show remove buttons if more than one row
                updateRemoveButtons();
            }

            function removeSubjectRow(row) {
                row.remove();
                updateRemoveButtons();
                reindexSubjects();
            }

            function createSubjectRow(index) {
                const row = document.createElement('div');
                row.className = 'subject-row mb-3 p-3 border rounded';

                const subjects = @json($subjects);

                row.innerHTML = `
                    <div class="row">
                        <div class="col-md-5">
                            <label>Mata Pelajaran</label>
                            <select name="subjects[${index}][subject_id]" class="form-control custom-select subject-select" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                ${subjects.map(subject => `<option value="${subject.id}">${subject.subject_name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Jumlah Soal</label>
                            <input type="number" name="subjects[${index}][question_count]" class="form-control question-count" placeholder="Jumlah soal" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label>Durasi (menit)</label>
                            <input type="number" name="subjects[${index}][duration]" class="form-control subject-duration" placeholder="Durasi" min="1" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-subject"><i class="fas fa-trash"></i></button>
                            <button type="button" class="btn btn-success add-subject ml-2"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                `;

                return row;
            }

            function updateRemoveButtons() {
                const rows = document.querySelectorAll('.subject-row');
                const removeButtons = document.querySelectorAll('.remove-subject');

                if (rows.length > 1) {
                    removeButtons.forEach(btn => btn.style.display = 'inline-block');
                } else {
                    removeButtons.forEach(btn => btn.style.display = 'none');
                }
            }

            function reindexSubjects() {
                const rows = document.querySelectorAll('.subject-row');
                rows.forEach((row, index) => {
                    const select = row.querySelector('.subject-select');
                    const input = row.querySelector('.question-count');
                    const durationInput = row.querySelector('.subject-duration');

                    if (select) select.name = `subjects[${index}][subject_id]`;
                    if (input) input.name = `subjects[${index}][question_count]`;
                    if (durationInput) durationInput.name = `subjects[${index}][duration]`;
                });
                subjectIndex = rows.length;
            }

            // Initialize
            updateRemoveButtons();
        });
    </script>
@endsection
