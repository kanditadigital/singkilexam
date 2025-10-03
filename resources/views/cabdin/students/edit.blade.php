@extends('layouts.main')

@section('content')
<div class="section-body">
    <div class="card shadow border-0">
        <!-- Header -->
        <div class="card-header bg-primary text-white py-2">
            <h4 class="mb-0">
                <i class="fas fa-user-edit"></i> Edit Siswa
            </h4>
        </div>

        <!-- Body -->
        <div class="card-body">
            <form action="{{ route('cabdin.students.update', $student) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Sekolah --}}
                <div class="form-group">
                    <label class="font-weight-semibold">Sekolah <span class="text-danger">*</span></label>
                    <select name="school_id" class="form-control @error('school_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $student->school_id) == $school->id ? 'selected' : '' }}>
                                {{ $school->school_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nama & NISN --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="student_name"
                               value="{{ old('student_name', $student->student_name) }}"
                               class="form-control @error('student_name') is-invalid @enderror"
                               placeholder="Nama lengkap siswa" required>
                        @error('student_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-semibold">NISN <span class="text-danger">*</span></label>
                        <input type="text" name="student_nisn"
                               value="{{ old('student_nisn', $student->student_nisn) }}"
                               class="form-control @error('student_nisn') is-invalid @enderror"
                               placeholder="Nomor Induk Siswa Nasional" required>
                        @error('student_nisn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Gender & Foto --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="student_gender" class="form-control @error('student_gender') is-invalid @enderror" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" {{ old('student_gender', $student->student_gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('student_gender', $student->student_gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('student_gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="font-weight-semibold">Foto Siswa <small class="text-muted">(Opsional)</small></label>
                        <input type="file" name="student_photo"
                               class="form-control-file @error('student_photo') is-invalid @enderror"
                               accept="image/*">
                        @error('student_photo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        @if ($student->student_photo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/'.$student->student_photo) }}" alt="Foto Siswa"
                                     class="img-thumbnail" width="120">
                                <small class="text-muted d-block mt-1">Foto saat ini</small>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('cabdin.students.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
