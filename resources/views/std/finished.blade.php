@extends('std.main')

@section('content')
    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm finish-card">
                    <div class="card-body p-5">
                        <div class="text-center">
                            <h3 class="mb-3">Ujian Selesai</h3>
                            <p class="text-muted mb-2">Terima kasih telah berpartisipasi dalam ujian ini</p>
                        </div>
                        <hr>
                        @php $subjectLabel = optional($attempt->session)->subject_display_name; @endphp
                        @if(!empty($subjectLabel))
                            <h4>{{ $subjectLabel }}</h4>
                        @endif
                        @if($grade)
                            @php
                                $correct = (int) ($grade->correct_questions ?? 0);
                                $answered = (int) ($grade->answered_questions ?? 0);
                                $wrong = max(0, $answered - $correct);
                            @endphp
                            <table class="table-borderless table-sm">
                                <tr><th>Nilai</th><td>:</td><td><span class="badge bg-success text-white" style="font-size: 1.6rem;">{{ number_format($grade->score, 2) }}</span></td></tr>
                                <tr><th>Soal Terjawab</th><td>:</td><td><span class="badge bg-warning text-dark">{{ $answered }}/{{ $grade->total_questions }}</span></td></tr>
                                <tr><th>Jawaban Benar</th><td>:</td><td><span class="badge bg-success text-white"><i class="fas fa-fw fa-check"></i> {{ $correct }}</span></td></tr>
                                <tr><th>Jawaban Salah</th><td>:</td><td><span class="badge bg-danger text-white"><i class="fas fa-fw fa-times"></i> {{ $wrong }}</span></td></tr>
                            </table>
                        @endif
                        <hr>
                        <div class="text-center">
                            <form action="{{ route('std.out') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-exam-danger mt-3 px-4">Keluar <i class="fas fa-fw fa-sign-out-alt"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
