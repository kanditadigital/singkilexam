@extends('std.main')

@section('content')
    <div class="section-body mb-5">
        @if ($sessions->isEmpty())
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body p-5 text-center text-danger">
                            <h4><i class="fas fa-fw fa-info-circle"></i> Tidak ada sesi ujian yang tersedia</h4>
                            <p class="mb-4">Silakan hubungi panitia ujian untuk memastikan jadwal dan token ujian Anda.</p>
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('std.out') }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-exam-danger px-3">
                                        Keluar <i class="fas fa-fw fa-sign-out-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Daftar sesi --}}
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Daftar Sesi Ujian</h4>
                            <p class="text-muted mb-4">
                                {{ $hasActiveSessions ? 'Sesi berikut sedang berlangsung.' : 'Belum ada sesi aktif, namun Anda dapat melihat jadwal yang sudah terdaftar.' }}
                            </p>
                            <div class="list-group">
                                @foreach ($sessions as $sessionItem)
                                    <div class="list-group-item flex-column align-items-start">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">{{ $sessionItem->exam->exam_name ?? '-' }} <div class="badge bg-danger text-white px-3 py-2">{{ $sessionItem->exam->exam_code ?? '-' }}</div></h5>
                                            <small class="text-muted">{{ $sessionItem->session_number }}</small>
                                        </div>
                                        <div class="d-flex flex-wrap text-muted small">
                                            <span class="mr-3">
                                                <i class="fas fa-clock"></i>
                                                {{ optional($sessionItem->session_start_time)->format('d M Y H:i') ?? '-' }}
                                                -
                                                {{ optional($sessionItem->session_end_time)->format('H:i') ?? '-' }}
                                            </span>
                                            <span class="mr-3"><i class="fas fa-hourglass-half"></i> Durasi {{ $sessionItem->session_duration }} menit</span>
                                            @if(!empty($sessionItem->subject_display_name))
                                                <span class="mr-3"><i class="fas fa-book"></i> {{ $sessionItem->subject_display_name }}</span>
                                            @endif
                                            <span class="badge {{ $sessionItem->session_status === 'Active' ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $sessionItem->session_status }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form peserta --}}
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body p-4">
                            <form action="{{ route('std.checktoken') }}" method="POST">
                                @csrf
                                <h3 class="mb-4">Konfirmasi data peserta</h3>
                                <div class="form-group">
                                    <label>Nama Peserta</label>
                                    <input type="text" class="form-control" value="{{ $participant['name'] }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Jenis Peserta</label>
                                    <input type="text" class="form-control" value="{{ $participantLabel }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Jenis Kelamin</label>
                                    <input type="text" class="form-control" value="{{ $participant['gender'] ?? '-' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Pilih Mata Ujian</label>
                                    <select name="exam_session_id" class="form-control custom-select @error('exam_session_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Sesi --</option>
                                        @foreach ($sessions as $sessionItem)
                                            <option value="{{ $sessionItem->id }}" {{ old('exam_session_id') == $sessionItem->id ? 'selected' : '' }}>
                                                {{ $sessionItem->exam->exam_name ?? '-' }} ({{ $sessionItem->exam->exam_code ?? '-' }})@if(!empty($sessionItem->subject_display_name)) - {{ $sessionItem->subject_display_name }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('exam_session_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Konfirmasi Nama Peserta</label>
                                    <input type="text" class="form-control @error('confirm_participant_name') is-invalid @enderror"
                                           name="confirm_participant_name" placeholder="Ketik ulang nama peserta"
                                           value="{{ old('confirm_participant_name') }}">
                                    @error('confirm_participant_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Token</label>
                                    <input type="text" class="form-control @error('exam_token') is-invalid @enderror"
                                           name="exam_token" placeholder="TOKEN" value="{{ old('exam_token') }}" required>
                                    @error('exam_token')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-block btn-exam">Mulai Ujian</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Reset Attempt tampil otomatis jika in_progress --}}
    @if($ongoingAttempt && ($ongoingAttempt->status === 'in_progress' || $ongoingAttempt->status !== 'submitted'))
    <div class="modal fade" id="resetAttemptModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="resetAttemptLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="resetAttemptLabel"><i class="fas fa-fw fa-list-alt"></i> Ujian Sedang Berlangsung</h5>
                </div>
                <div class="modal-body">
                    <p>Anda memiliki ujian yang sedang berlangsung.</p>
                    <table class="borderless table-sm">
                        <tr>
                            <th>Mata Pelajaran</th>
                            <td>:</td>
                            <td>{{ optional($ongoingAttempt->session)->subject_display_name ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <a href="{{ route('std.exam', ['token' => $ongoingAttempt->exam->exam_code, 'session' => $ongoingAttempt->exam_session_id]) }}"
                       class="btn btn-sm btn-warning"><i class="fas fa-fw fa-sync"></i> Lanjutkan Ujian</a>

                    <form action="{{ route('std.reset_attempt', $ongoingAttempt->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-fw fa-eraser"></i> Reset Ujian</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script auto show modal --}}
    @push('scripts')
    <script>
        $(document).ready(function () {
            $('#resetAttemptModal').modal('show');
        });
    </script>
    @endpush
    @endif
@endsection
