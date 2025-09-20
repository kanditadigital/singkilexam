@extends('std.main')

@section('content')
    <div class="section-body mb-5">
        @if ($data->session_status === 'Inactive')
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body p-5 text-center text-danger">
                            <h4><i class="fas fa-fw fa-info-circle"></i> Tidak ada sesi ujian yang berlangsung</h4>
                            <div class="d-flex justify-content-center mt-4">
                                <button class="btn btn-exam mr-3 px-3" id="btnrefresh" onclick="btnRefresh()"><i class="fas fa-fw fa-sync"></i> Refresh</button>
                                <form action="{{ route('signout') }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-exam-danger px-3">Keluar <i class="fas fa-fw fa-sign-out-alt"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body p-5">
                            <h2 class="mb-4">TOKEN : {{ optional($data->exam)->exam_code ?? '-' }}</h2>
                            <div class="form-group">
                                <label>Nama Tes</label>
                                <p class="text-confirmation">{{ optional($data->subject)->subject_name ?? '-' }}</p>
                            </div>
                            <div class="form-group">
                                <label>Status Tes</label>
                                <p class="text-confirmation">{{ $data->session_status ?? '-' }}</p>
                            </div>
                            <div class="form-group">
                                <label>Waktu Tes</label>
                                <p class="text-confirmation">{{ $data->session_duration ?? '-' }} Menit</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body p-4">
                            <form action="{{ route('std.checktoken') }}" method="POST">
                                @csrf
                                <h3 class="mb-4">Konfirmasi data peserta</h3>
                                <div class="form-group">
                                    <label>Nama Peserta</label>
                                    <input type="text" class="form-control" name="student_name" value="{{ Auth::guard('students')->user()->student_name }}" @readonly(true)>
                                </div>
                                <div class="form-group">
                                    <label>Jenis Kelamin</label>
                                    <input type="text" class="form-control" name="student_gender" value="{{ Auth::guard('students')->user()->student_gender }}" @readonly(true)>
                                </div>
                                <div class="form-group">
                                    <label>Mata Ujian</label>
                                    <select name="mapel_id" class="form-control custom-select"  @readonly(true)>
                                        <option value="1">Mata Pelajaran Wajib</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nama Peserta</label>
                                    <input type="text" class="form-control" name="confirm_student_name" placeholder="Nama Peserta">
                                </div>
                                <div class="form-group">
                                    <label>Token</label>
                                    <input type="text" class="form-control" name="exam_token" placeholder="TOKEN" @required(true)>
                                </div>
                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-block btn-exam">MULAI UJIAN</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function btnRefresh() {
            window.location.reload();
        }
    </script>
@endpush
