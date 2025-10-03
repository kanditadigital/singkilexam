@extends('layouts.main')

@section('content')
<div class="section-body">
    <div class="card shadow border-0">
        <!-- Header -->
        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-edit"></i> Edit Sekolah
            </h4>
            <a href="{{ route('cabdin.schools.index') }}" class="btn btn-reka btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Body -->
        <div class="card-body">
            <form action="{{ route('cabdin.schools.update', $school) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="form-row">
                    <!-- NPSN -->
                    <div class="form-group col-md-6">
                        <label for="school_npsn" class="font-weight-semibold">
                            NPSN <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="school_npsn"
                               name="school_npsn"
                               value="{{ old('school_npsn', $school->school_npsn) }}"
                               class="form-control @error('school_npsn') is-invalid @enderror"
                               placeholder="Masukkan NPSN Sekolah"
                               required>
                        @error('school_npsn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group col-md-6">
                        <label for="email" class="font-weight-semibold">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email', $school->email) }}"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="contoh: sekolah@email.com"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <!-- Nama Sekolah -->
                    <div class="form-group col-md-6">
                        <label for="school_name" class="font-weight-semibold">
                            Nama Sekolah <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="school_name"
                               name="school_name"
                               value="{{ old('school_name', $school->school_name) }}"
                               class="form-control @error('school_name') is-invalid @enderror"
                               placeholder="Masukkan nama sekolah"
                               required>
                        @error('school_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="form-group col-md-6">
                        <label for="school_phone" class="font-weight-semibold">
                            Nomor Telepon <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="school_phone"
                               name="school_phone"
                               value="{{ old('school_phone', $school->school_phone) }}"
                               class="form-control @error('school_phone') is-invalid @enderror"
                               placeholder="Contoh: 0812xxxx"
                               required>
                        @error('school_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <!-- Alamat -->
                    <div class="form-group col-md-6">
                        <label for="school_address" class="font-weight-semibold">
                            Alamat Sekolah <span class="text-danger">*</span>
                        </label>
                        <textarea id="school_address"
                                  name="school_address"
                                  rows="3"
                                  class="form-control @error('school_address') is-invalid @enderror"
                                  placeholder="Masukkan alamat lengkap sekolah"
                                  style="min-height: 120px;"
                                  required>{{ old('school_address', $school->school_address) }}</textarea>
                        @error('school_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group col-md-6">
                        <label for="password" class="font-weight-semibold">
                            Password (Opsional)
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Kosongkan jika tidak ingin mengganti password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Biarkan kosong jika password tidak perlu diubah.
                        </small>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
