@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="row">
            <div class="col-md-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-school"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Sekolah</h4></div>
                        <div class="card-body">{{ $schoolsCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-school"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Sekolah Aktif</h4></div>
                        <div class="card-body">{{ $activeSchools }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-user-friends"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Siswa</h4></div>
                        <div class="card-body">{{ $studentsCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-primary text-white py-2">
                <h4 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Cabdin</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Nama Cabdin</dt>
                    <dd class="col-sm-9">{{ $branch->branch_name }}</dd>

                    <dt class="col-sm-3 text-muted">Email</dt>
                    <dd class="col-sm-9">{{ $branch->email }}</dd>

                    <dt class="col-sm-3 text-muted">No. Telepon</dt>
                    <dd class="col-sm-9">{{ $branch->branch_phone }}</dd>

                    <dt class="col-sm-3 text-muted">Alamat</dt>
                    <dd class="col-sm-9">{{ $branch->branch_address }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .stats-card {
        border-radius: 16px;
    }
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
</style>
@endpush
