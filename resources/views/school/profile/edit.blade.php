@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-edit"></i> Edit Profil Sekolah</h4>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('sch.profile.update') }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="school_name">Nama Sekolah <span class="text-danger">*</span></label>
                            <input type="text" name="school_name" id="school_name" class="form-control @error('school_name') is-invalid @enderror" value="{{ old('school_name', $school->school_name) }}" placeholder="Masukkan nama sekolah">
                            @error('school_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email">Email Sekolah <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $school->email) }}" placeholder="Masukkan email sekolah">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <hr>
                    <h5>Ubah Password (Opsional)</h5>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="current_password">Password Saat Ini</label>
                            <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Masukkan password saat ini">
                            @error('current_password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="new_password">Password Baru</label>
                            <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Masukkan password baru">
                            @error('new_password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Konfirmasi password baru">
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
