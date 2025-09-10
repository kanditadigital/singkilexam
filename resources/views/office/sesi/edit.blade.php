@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-list"></i> Edit Sesi Ujian</h4>
                <div class="ml-auto">
                    <a href="{{ route('sesi-ujian.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('sesi-ujian.update', $sesi->id) }}" method="post">
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
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="subject_name">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subject_id" class="form-control custom-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ (old('subject_id') ?? $sesi->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                            @error('subject_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="duration">Durasi (menit) <span class="text-danger">*</span></label>
                            <input type="number" name="session_duration" id="session_duration" class="form-control @error('session_duration') is-invalid @enderror" value="{{ old('session_duration') ?? $sesi->session_duration }}" placeholder="Masukkan durasi dalam menit" required>
                            @error('session_duration')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
@endsection
