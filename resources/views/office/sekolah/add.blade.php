@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-list"></i> Tambah Sekolah</h4>
                <div class="ml-auto">
                    <a href="{{ route('disdik.sekolah.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('disdik.sekolah.store') }}" method="post">
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="branch_id">Nama Cabdin <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-control custom-select @error('branch_id') is-invalid @enderror">
                                <option value="">Pilih Cabdin</option>
                                @foreach ($cabdin as $item)
                                    <option value="{{ $item->id }}">{{ $item->branch_name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="school_npsn">NPSN <span class="text-danger">*</span></label>
                            <input type="text" name="school_npsn" id="school_npsn" class="form-control @error('school_npsn') is-invalid @enderror" value="{{ old('school_npsn') }}" placeholder="Masukkan NPSN">
                            @error('school_npsn')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="school_name">Nama Sekolah <span class="text-danger">*</span></label>
                            <input type="text" name="school_name" id="school_name" class="form-control @error('school_name') is-invalid @enderror" value="{{ old('school_name') }}" placeholder="Masukkan nama sekolah">
                            @error('school_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Masukkan email">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="school_phone">No. Telepon Sekolah <span class="text-danger">*</span></label>
                            <input type="text" name="school_phone" id="school_phone" class="form-control @error('school_phone') is-invalid @enderror" value="{{ old('school_phone') }}" placeholder="Masukkan no. telepon">
                            @error('school_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="school_address">Alamat Sekolah <span class="text-danger">*</span></label>
                            <input type="text" name="school_address" id="school_address" class="form-control @error('school_address') is-invalid @enderror" value="{{ old('school_address') }}" placeholder="Masukkan alamat">
                            @error('school_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
