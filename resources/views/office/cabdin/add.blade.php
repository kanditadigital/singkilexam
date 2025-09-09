@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-list"></i> Tambah Cabdin</h4>
                <div class="ml-auto">
                    <a href="{{ route('cabdin.index') }}" class="btn btn-reka"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body mb-0">
                <form action="{{ route('cabdin.store') }}" method="post">
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="branch_name">Nama Cabdin <span class="text-danger">*</span></label>
                            <input type="text" name="branch_name" id="branch_name" class="form-control @error('branch_name') is-invalid @enderror" value="{{ old('branch_name') }}" placeholder="Masukkan nama cabdin">
                            @error('branch_name')
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
                            <label for="branch_phone">No. Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="branch_phone" id="branch_phone" class="form-control @error('branch_phone') is-invalid @enderror" value="{{ old('branch_phone') }}" placeholder="Masukkan no. telepon">
                            @error('branch_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="branch_address">Alamat <span class="text-danger">*</span></label>
                            <input type="text" name="branch_address" id="branch_address" class="form-control @error('branch_address') is-invalid @enderror" value="{{ old('branch_address') }}" placeholder="Masukkan alamat">
                            @error('branch_address')
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
