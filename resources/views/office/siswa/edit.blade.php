@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-list"></i> Edit Siswa</h4>
                <div class="ml-auto">
                    <a href="{{ route('siswa.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('siswa.update', $siswa->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="branch_id">Nama Cabdin <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-control custom-select @error('branch_id') is-invalid @enderror">
                                <option value="">Pilih Cabdin</option>
                                @foreach ($cabdin as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $siswa->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="school_id">Nama Sekolah <span class="text-danger">*</span></label>
                            <select name="school_id" id="school_id" class="form-control custom-select @error('school_id') is-invalid @enderror">
                                <option value="">Pilih Sekolah</option>
                                <option value="{{ $siswa->school_id }}" selected>{{ $siswa->school->school_name }}</option>
                            </select>
                            @error('school_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="student_name">Nama Siswa <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" id="student_name" class="form-control @error('student_name') is-invalid @enderror" value="{{ old('student_name', $siswa->student_name) }}" placeholder="Masukkan nama siswa">
                            @error('student_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="student_nisn">NISN <span class="text-danger">*</span></label>
                            <input type="text" name="student_nisn" id="student_nisn" class="form-control @error('student_nisn') is-invalid @enderror" value="{{ old('student_nisn', $siswa->student_nisn) }}" placeholder="Masukkan NISN">
                            @error('student_nisn')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="student_gender">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="student_gender" id="student_gender" class="form-control custom-select @error('student_gender') is-invalid @enderror">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('student_gender', $siswa->student_gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('student_gender', $siswa->student_gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('student_gender')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="student_photo">Foto Siswa</label>
                            <input type="file" name="student_photo" id="student_photo" class="form-control @error('student_photo') is-invalid @enderror" accept="image/*">
                            @error('student_photo')
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

@push('scripts')
<script>
document.getElementById('branch_id').addEventListener('change', function() {
    let branchId = this.value;
    let schoolDropdown = document.getElementById('school_id');
    schoolDropdown.innerHTML = '<option value="">Loading...</option>';

    if(branchId) {
        fetch(`/disdik/siswa/by-branch/${branchId}`)
            .then(response => response.json())
            .then(data => {
                schoolDropdown.innerHTML = '<option value="">Pilih Sekolah</option>';
                data.forEach(function(school) {
                    schoolDropdown.innerHTML += `<option value="${school.id}">${school.school_name}</option>`;
                });
            });
    } else {
        schoolDropdown.innerHTML = '<option value="">Pilih Sekolah</option>';
    }
});
</script>
@endpush
