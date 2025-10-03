@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="row">
            <div class="col-md-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-clipboard-list"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Ujian</h4></div>
                        <div class="card-body">{{ $examcount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-clipboard-list"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Pelajaran</h4></div>
                        <div class="card-body">{{ $subjectcount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="far fa-edit"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Sesi Ujian</h4></div>
                        <div class="card-body">{{ $examsession }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-building"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Cabdin</h4></div>
                        <div class="card-body">{{ $branchcount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-school"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Sekolah</h4></div>
                        <div class="card-body">{{ $schcount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-users"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Guru & Staff</h4></div>
                        <div class="card-body">{{ $employeecount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary"><i class="fas fa-user-friends"></i></div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Jumlah Siswa</h4></div>
                        <div class="card-body">{{ $stdcount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
