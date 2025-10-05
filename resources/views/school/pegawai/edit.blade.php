@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-edit"></i> Edit Guru & Staff</h4>
                <div class="ml-auto">
                    <a href="{{ route('sch.employee.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('sch.employee.update', $employee->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="employee_name">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="employee_name" id="employee_name" class="form-control @error('employee_name') is-invalid @enderror" value="{{ old('employee_name', $employee->employee_name) }}" placeholder="Masukkan nama guru/staff">
                            @error('employee_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" value="{{ $employee->username }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}" placeholder="Masukkan email (opsional)">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="employee_phone">Nomor Telepon</label>
                            <input type="text" name="employee_phone" id="employee_phone" class="form-control @error('employee_phone') is-invalid @enderror" value="{{ old('employee_phone', $employee->employee_phone) }}" placeholder="Masukkan nomor telepon">
                            @error('employee_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="employee_type">Tipe Pegawai <span class="text-danger">*</span></label>
                        <select name="employee_type" id="employee_type" class="form-control custom-select @error('employee_type') is-invalid @enderror">
                            <option value="">Pilih tipe pegawai</option>
                            <option value="Guru" {{ old('employee_type', $employee->employee_type) === 'Guru' ? 'selected' : '' }}>Guru</option>
                            <option value="Staff" {{ old('employee_type', $employee->employee_type) === 'Staff' ? 'selected' : '' }}>Staff</option>
                            <option value="Kepala Sekolah" {{ old('employee_type', $employee->employee_type) === 'Kepala Sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                        </select>
                        @error('employee_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="reset_password" value="1" id="reset_password" class="form-check-input">
                        <label class="form-check-label" for="reset_password">Reset password (password baru akan dibuat otomatis)</label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
