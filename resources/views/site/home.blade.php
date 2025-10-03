@extends('site.main')

@section('content')
    <div class="home-header d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card card-login shadow">
                        <div class="card-body">
                            <div class="my-4 text-center">
                                <h5>Member Area</h5>
                            </div>
                            <form action="{{ route('ayo.login') }}" method="POST">
                                @csrf
                                <div class="input-group flex-nowrap mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Email" autocomplete="off" required>
                                </div>
                                <div class="input-group mb-4">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="off" required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="d-flex align-items-center">
                                        <img id="captcha-img" src="{{ captcha_src('flat') }}" alt="captcha" style="width: 100%; height:auto;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="refresh-captcha">
                                            <i class="fa-solid fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-barcode"></i></span>
                                    <input type="text" class="form-control" name="captcha" placeholder="Kode Keamanan" required>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="remember">Ingat saya</label>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-cat w-100">Login</button>
                                </div>
                        </form>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    <div class="home-about p-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <h4>Apa Itu Assesmen Nasional?</h4>
                </div>
            </div>
        </div>
    </div>
    @if($publicLiveScoreEnabled)
        <div class="live-score-public py-5">
            <div class="container">
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-6">
                        <h3 class="mb-2"><i class="fa-solid fa-chart-line text-primary"></i> Live Score Ujian</h3>
                        <p class="text-muted mb-0">Pantau nilai peserta secara realtime. Data diperbarui otomatis.</p>
                    </div>
                    <div class="col-lg-6 text-lg-right mt-3 mt-lg-0">
                        <span class="badge badge-success" id="public-live-score-status">Aktif</span>
                        <span class="text-muted small d-block mt-2" id="public-live-score-updated">Menunggu data...</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="public-filter-exam" class="small text-uppercase text-muted">Pilih Ujian</label>
                        <select id="public-filter-exam" class="form-control">
                            <option value="">Semua Ujian</option>
                            @foreach($publicLiveScoreExams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->exam_name }} ({{ $exam->exam_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="public-filter-branch" class="small text-uppercase text-muted">Cabdin</label>
                        <select id="public-filter-branch" class="form-control">
                            <option value="">Semua Cabdin</option>
                            @foreach($publicLiveScoreBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="public-filter-school" class="small text-uppercase text-muted">Sekolah</label>
                        <select id="public-filter-school" class="form-control" disabled>
                            <option value="">Semua Sekolah</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive shadow-sm rounded overflow-hidden">
                    <table class="table table-striped mb-0" id="public-live-score-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Peserta</th>
                                <th>Cabdin</th>
                                <th>Sekolah</th>
                                <th class="text-right">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Memuat data live score...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('refresh-captcha').addEventListener('click', function () {
            fetch('/refresh-captcha')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('captcha-img').src = data.captcha;
                });
        });
        @if($publicLiveScoreEnabled)
        const publicRoutes = {
            data: @json(route('public.live-score')),
            schoolsByBranch: @json(route('public.live-score.schools', ['branch' => '__BRANCH__'])),
        };

        const liveScoreTableBody = $('#public-live-score-table tbody');
        const liveScoreUpdatedEl = $('#public-live-score-updated');
        const liveScoreStatusEl = $('#public-live-score-status');
        const branchSelect = $('#public-filter-branch');
        const schoolSelect = $('#public-filter-school');
        const examSelect = $('#public-filter-exam');
        let publicLiveScoreTimer = null;

        function updateStatusLabel(isLoading) {
            if (isLoading) {
                liveScoreStatusEl.text('Memuat data...');
            }
        }

        function updateBadge(enabled) {
            if (enabled) {
                $('#public-live-score-status')
                    .removeClass('badge-secondary')
                    .addClass('badge-success')
                    .text('Aktif');
            } else {
                $('#public-live-score-status')
                    .removeClass('badge-success')
                    .addClass('badge-secondary')
                    .text('Nonaktif');
            }
        }

        function buildParams() {
            return {
                exam_id: examSelect.val() || '',
                branch_id: branchSelect.val() || '',
                school_id: schoolSelect.val() || '',
            };
        }

        function renderLiveScoreRows(items) {
            if (!items || items.length === 0) {
                liveScoreTableBody.html('<tr><td colspan="4" class="text-center text-muted py-4">Belum ada nilai yang tersedia.</td></tr>');
                return;
            }

            const rows = items.map(function (item) {
                const score = item.score_formatted || '-';
                return `
                    <tr>
                        <td>
                            <div class="font-weight-semibold">${item.participant_name || '-'}</div>
                            <div class="small text-muted">${item.participant_label || ''}</div>
                        </td>
                        <td>${item.branch_name || '-'}</td>
                        <td>${item.school_name || '-'}</td>
                        <td class="text-right"><strong>${score}</strong></td>
                    </tr>
                `;
            }).join('');

            liveScoreTableBody.html(rows);
        }

        function fetchPublicLiveScore() {
            const params = buildParams();
            const query = new URLSearchParams(params).toString();
            updateStatusLabel(true);

            fetch(publicRoutes.data + '?' + query)
                .then(function (response) {
                    if (!response.ok) {
                        if (response.status === 403) {
                            liveScoreStatusEl.text('Live score publik sedang nonaktif.');
                            liveScoreStatusEl.removeClass('text-muted').addClass('text-danger');
                            updateBadge(false);
                            return response.json();
                        }
                        throw new Error('Gagal memuat data');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    if (!payload) {
                        return;
                    }
                    if (payload.data) {
                        updateBadge(true);
                        renderLiveScoreRows(payload.data);
                    }
                    if (payload.generated_at) {
                        liveScoreStatusEl.text('Diperbarui: ' + new Date(payload.generated_at).toLocaleString('id-ID'));
                    }
                })
                .catch(function () {
                    liveScoreTableBody.html('<tr><td colspan="4" class="text-center text-danger py-4">Terjadi kesalahan saat memuat data.</td></tr>');
                    liveScoreStatusEl.text('Tidak dapat memuat data.');
                });
        }

        function startPublicLiveScore() {
            if (publicLiveScoreTimer) {
                clearInterval(publicLiveScoreTimer);
            }
            publicLiveScoreTimer = setInterval(fetchPublicLiveScore, 10000);
        }

        function loadPublicSchools(branchId) {
            if (!branchId) {
                schoolSelect.prop('disabled', false).html('<option value="">Semua Sekolah</option>');
                fetchPublicLiveScore();
                return;
            }
            schoolSelect.prop('disabled', true).html('<option value="">Memuat sekolah...</option>');
            const url = publicRoutes.schoolsByBranch.replace('__BRANCH__', branchId);
            fetch(url)
                .then(function (response) { return response.json(); })
                .then(function (schools) {
                    const options = schools.map(function (school) {
                        return `<option value="${school.id}">${school.school_name}</option>`;
                    }).join('');
                    schoolSelect.html('<option value="">Semua Sekolah</option>' + options).prop('disabled', false);
                    fetchPublicLiveScore();
                })
                .catch(function () {
                    schoolSelect.html('<option value="">Gagal memuat sekolah</option>').prop('disabled', false);
                });
        }

        examSelect.on('change', fetchPublicLiveScore);
        branchSelect.on('change', function () {
            const selectedBranch = $(this).val();
            loadPublicSchools(selectedBranch);
        });
        schoolSelect.on('change', fetchPublicLiveScore);

        fetchPublicLiveScore();
        startPublicLiveScore();
        updateBadge(true);

        window.addEventListener('beforeunload', function () {
            if (publicLiveScoreTimer) {
                clearInterval(publicLiveScoreTimer);
            }
        });
        @endif
    });
    </script>

@endpush
