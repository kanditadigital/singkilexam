@extends('std.main')

@section('content')
    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body p-5">
                        <form action="" method="POST">
                            @csrf
                            <h2 class="mb-4">Konfirmasi Tes</h2>
                            <div class="form-group">
                                <label>Nama Tes</label>
                                <p class="text-confirmation">Mata Pelajaran Wajib</p>
                            </div>
                            <div class="form-group">
                                <label>Status Tes</label>
                                <p class="text-confirmation">Tes Baru</p>
                            </div>
                            <div class="form-group">
                                <label>Waktu Tes</label>
                                <p class="text-confirmation">100 Menit</p>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-block btn-exam">MULAI</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
