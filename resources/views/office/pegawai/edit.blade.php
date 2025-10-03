@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-list"></i> Edit Pegawai</h4>
                <div class="ml-auto">
                    <a href="{{ route('disdik.pegawai.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('disdik.pegawai.update', $pegawai->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="branch_id">Nama Cabdin <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-control custom-select @error('branch_id') is-invalid @enderror">
                                <option value="">Pilih Cabdin</option>
                                @foreach ($cabdin as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $pegawai->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
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
                                <option value="{{ $pegawai->school_id }}" selected>{{ $pegawai->school->school_name }}</option>
                            </select>
                            @error('school_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="employee_name">Nama Pegawai <span class="text-danger">*</span></label>
                            <input type="text" name="employee_name" id="employee_name" class="form-control @error('employee_name') is-invalid @enderror" value="{{ old('employee_name', $pegawai->employee_name) }}" placeholder="Masukkan nama pegawai">
                            @error('employee_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $pegawai->email) }}" placeholder="Masukkan email">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="employee_type">Tipe Pegawai <span class="text-danger">*</span></label>
                            <select name="employee_type" id="employee_type" class="form-control custom-select @error('employee_type') is-invalid @enderror">
                                <option value="">Pilih Tipe Pegawai</option>
                                <option value="Guru" {{ old('employee_type', $pegawai->employee_type) == 'Guru' ? 'selected' : '' }}>Guru</option>
                                <option value="Staff" {{ old('employee_type', $pegawai->employee_type) == 'Staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                            @error('employee_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="employee_phone">No. Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="employee_phone" id="employee_phone" class="form-control @error('employee_phone') is-invalid @enderror" value="{{ old('employee_phone', $pegawai->employee_phone) }}" placeholder="Masukkan no. telepon">
                            @error('employee_phone')
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
        fetch(`/disdik/schools/by-branch/${branchId}`)
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
