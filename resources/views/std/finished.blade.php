@extends('std.main')

@section('content')
    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm finish-card">
                    <div class="card-body p-5 text-center">
                        <h3 class="mb-3">Ujian Selesai</h3>
                        <p class="text-muted mb-2">Terima kasih telah menyelesaikan ujian: <strong>{{ $exam->exam_name }}</strong>.</p>
                        @if(optional($attempt->session->subject)->subject_name)
                            <p class="mb-3">Mata Ujian: <strong>{{ $attempt->session->subject->subject_name }}</strong></p>
                        @endif
                        @if($grade)
                            @php
                                $correct = (int) ($grade->correct_questions ?? 0);
                                $answered = (int) ($grade->answered_questions ?? 0);
                                $wrong = max(0, $answered - $correct);
                            @endphp
                            <p class="lead mb-1">Nilai Anda: <strong>{{ number_format($grade->score, 2) }}</strong></p>
                            <p class="text-muted mb-2">Soal terjawab: {{ $answered }}/{{ $grade->total_questions }}</p>
                            <p class="mb-0">Jawaban benar: <strong>{{ $correct }}</strong></p>
                            <p class="mb-3">Jawaban salah: <strong>{{ $wrong }}</strong></p>
                        @endif
                        <form action="{{ route('std.out') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-exam-danger mt-3 px-4">Keluar <i class="fas fa-fw fa-sign-out-alt"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
