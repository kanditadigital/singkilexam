@extends('site.main')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h4 class="card-title mb-0 py-3"><i class="fas fa-fw fa-download"></i> Download Exambrowser</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead">Pilih versi Exambrowser yang sesuai dengan sistem operasi Anda untuk mengikuti ujian online.</p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary mb-3">
                                    <div class="card-body text-center">
                                        <i class="fab fa-windows fa-3x text-primary mb-3"></i>
                                        <h5 class="card-title">Windows</h5>
                                        <p class="card-text">Versi untuk sistem operasi Windows.</p>
                                        <a href="https://drive.google.com/drive/folders/1yXyzGZg-DGtokmoANJRbFZqbVMRU3D4I?usp=sharing" target="_blank" class="btn btn-primary">
                                            <i class="fas fa-download"></i> Download untuk Windows
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success mb-3">
                                    <div class="card-body text-center">
                                        <i class="fab fa-apple fa-3x text-success mb-3"></i>
                                        <h5 class="card-title">macOS</h5>
                                        <p class="card-text">Versi untuk sistem operasi macOS.</p>
                                        <a href="https://drive.google.com/drive/folders/11sEM1KF1uYCPZN3AW_4fHCUDDUg-eVlB?usp=sharing" target="_blank" class="btn btn-success">
                                            <i class="fas fa-download"></i> Download untuk macOS
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-info-circle"></i> Instruksi Instalasi:</h6>
                            <ol>
                                <li>Download file instalasi sesuai dengan sistem operasi Anda.</li>
                                <li>Jalankan file instalasi dan ikuti petunjuk yang muncul.</li>
                                <li>Setelah instalasi selesai, buka aplikasi Exambrowser untuk login dan mengikuti ujian.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
</style>
@endpush
