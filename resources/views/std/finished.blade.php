@extends('std.main')

@section('content')
    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm finish-card">
                    <div class="card-body p-5 text-center">
                        <h3 class="mb-3">Ujian Selesai</h3>
                        <p class="text-muted mb-2">Terima kasih telah menyelesaikan ujian: <strong>{{ $exam->exam_name }}</strong>.</p>
                        @if($grade)
                            <p class="lead mb-1">Nilai Anda: <strong>{{ number_format($grade->score, 2) }}</strong></p>
                            <p class="text-muted">Soal terjawab: {{ $grade->answered_questions }}/{{ $grade->total_questions }}</p>
                        @endif
                        <form action="{{ route('signout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-exam-danger mt-3 px-4">Keluar <i class="fas fa-fw fa-sign-out-alt"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
