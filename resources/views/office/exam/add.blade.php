@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-list"></i> Tambah Ujian</h4>
                <div class="ml-auto">
                    <a href="{{ route('disdik.exam.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('disdik.exam.store') }}" method="post">
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="exam_type">Tipe Ujian <span class="text-danger">*</span></label>
                            <select name="exam_type" id="exam_type" class="form-control custom-select @error('exam_type') is-invalid @enderror" required>
                                <option value="">Pilih Tipe Ujian</option>
                                <option value="UKOM" {{ old('exam_type') == 'UKOM' ? 'selected' : '' }}>UKOM</option>
                                <option value="TKA" {{ old('exam_type') == 'TKA' ? 'selected' : '' }}>TKA</option>
                                <option value="ANBK" {{ old('exam_type') == 'ANBK' ? 'selected' : '' }}>ANBK</option>
                            </select>
                            @error('exam_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="exam_name">Nama Ujian <span class="text-danger">*</span></label>
                            <input type="text" name="exam_name" id="exam_name" class="form-control @error('exam_name') is-invalid @enderror" value="{{ old('exam_name') }}" placeholder="Masukkan nama ujian" required>
                            @error('exam_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="exam_description">Deskripsi Ujian <span class="text-danger">*</span></label>
                            <textarea name="exam_description" id="texteditor">{{ old('exam_description') }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.tiny.cloud/1/lgwbcigxg5kj9r7fpvlp83nmg38onp3bntizwoibu78t09r5/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#texteditor',
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
            toolbar_mode: 'floating',
            height: 300,
        });
    </script>
@endpush
