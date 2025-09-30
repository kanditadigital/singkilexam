@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-broadcast-tower"></i> Live Score Ujian</h4>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="form-group col-md-4">
                        <label for="filter_branch">Cabang Dinas</label>
                        <select id="filter_branch" class="form-control custom-select">
                            <option value="">Semua Cabdin</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="filter_school">Sekolah</label>
                        <select id="filter_school" class="form-control custom-select" disabled>
                            <option value="">Semua Sekolah</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="filter_status">Status Ujian</label>
                        <select id="filter_status" class="form-control custom-select">
                            <option value="">Semua Status</option>
                            <option value="active">Sedang Berlangsung</option>
                            <option value="submitted">Sudah Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                    <div class="text-muted" id="generated-info">Memuat data...</div>
                    <div>
                        <button id="btn-refresh" class="btn btn-outline-primary btn-sm"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="live-score-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Peserta</th>
                                <th>Sekolah</th>
                                <th>Cabdin</th>
                                <th>Ujian</th>
                                <th>Sesi</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Jawaban</th>
                                <th>Nilai</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Pembaruan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">Belum ada data. Silakan pilih filter dan refresh.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    #live-score-table tbody tr td {
        vertical-align: middle;
    }
    .progress-wrapper {
        min-width: 140px;
    }
    .progress-label {
        font-size: 12px;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/id.min.js"></script>
<script>
    const routes = {
        schoolsByBranch: @json(route('pegawai.getByBranch', ['branchId' => '__BRANCH__'])),
        liveData: @json(route('live-score.data')),
    };

    const $tableBody = $('#live-score-table tbody');
    const $branchSelect = $('#filter_branch');
    const $schoolSelect = $('#filter_school');
    const $statusSelect = $('#filter_status');
    const $generatedInfo = $('#generated-info');
    const $refreshButton = $('#btn-refresh');

    let pollingTimer = null;
    const POLLING_INTERVAL = 10000; // 10 detik

    function formatStatus(status) {
        if (status === 'submitted') {
            return '<span class="badge badge-success">Selesai</span>';
        }
        if (status === 'in_progress') {
            return '<span class="badge badge-info">Berlangsung</span>';
        }
        return '<span class="badge badge-secondary">' + (status || '-') + '</span>';
    }

    function formatProgress(progress) {
        const safeProgress = Math.min(100, Math.max(0, progress || 0));
        const progressClass = safeProgress >= 100 ? 'bg-success' : 'bg-info';
        return `
            <div class="progress-wrapper">
                <div class="progress mb-1" style="height: 8px;">
                    <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${safeProgress}%" aria-valuenow="${safeProgress}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="progress-label text-muted">${safeProgress}%</div>
            </div>
        `;
    }

    function formatScore(score, status) {
        if (score === null || status !== 'submitted') {
            return '<span class="text-muted">-</span>';
        }
        return `<strong>${parseFloat(score).toFixed(2)}</strong>`;
    }

    function secondsToClock(seconds) {
        if (seconds === null) {
            return '-';
        }
        const sec = Math.max(0, parseInt(seconds, 10));
        const h = Math.floor(sec / 3600);
        const m = Math.floor((sec % 3600) / 60);
        const s = sec % 60;
        return [h, m, s]
            .map(val => val.toString().padStart(2, '0'))
            .join(':');
    }

    function buildRow(item) {
        const statusBadge = formatStatus(item.status);
        const progressBar = formatProgress(item.progress);
        const scoreCell = formatScore(item.score, item.status);
        const answeredInfo = `${item.answered_questions}/${item.total_questions}`;
        const startTime = item.started_at ? moment(item.started_at).format('DD MMM YYYY HH:mm') : '-';
        const submittedTime = item.submitted_at ? moment(item.submitted_at).format('DD MMM YYYY HH:mm') : '-';
        const updatedTime = item.updated_at ? moment(item.updated_at).fromNow() : '-';
        const remaining = item.status === 'in_progress' ? secondsToClock(item.remaining_seconds) : '-';

        return `
            <tr>
                <td>
                    <div class="font-weight-bold">${item.student_name || '-'}</div>
                    <div class="text-muted small">NISN: ${item.student_nisn || '-'}</div>
                </td>
                <td>${item.school_name || '-'}</td>
                <td>${item.branch_name || '-'}</td>
                <td>
                    <div class="font-weight-bold">${item.exam_name || '-'}</div>
                    <div class="text-muted small">Kode: ${item.exam_code || '-'}</div>
                </td>
                <td>${item.session_number || '-'}<br><span class="text-muted small">Sisa: ${remaining}</span></td>
                <td>${statusBadge}</td>
                <td>${progressBar}</td>
                <td>${answeredInfo}</td>
                <td>${scoreCell}</td>
                <td>${startTime}</td>
                <td>${submittedTime}</td>
                <td>${updatedTime}</td>
            </tr>
        `;
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            $tableBody.html('<tr><td colspan="12" class="text-center py-4 text-muted">Data belum tersedia.</td></tr>');
            return;
        }

        const rows = data.map(buildRow).join('');
        $tableBody.html(rows);
    }

    function fetchLiveScore() {
        const params = {
            branch_id: $branchSelect.val() || '',
            school_id: $schoolSelect.val() || '',
            status: $statusSelect.val() || '',
        };

        $tableBody.addClass('loading');

        $.getJSON(routes.liveData, params)
            .done(function (response) {
                renderTable(response.data || []);
                if (response.generated_at) {
                    const generated = moment(response.generated_at).format('DD MMM YYYY HH:mm:ss');
                    $generatedInfo.text('Terakhir diperbarui: ' + generated + ' WIB');
                } else {
                    $generatedInfo.text('Data berhasil dimuat.');
                }
            })
            .fail(function () {
                $tableBody.html('<tr><td colspan="12" class="text-center py-4 text-danger">Gagal memuat data live score.</td></tr>');
                $generatedInfo.text('Terjadi kesalahan saat memuat data.');
            })
            .always(function () {
                $tableBody.removeClass('loading');
            });
    }

    function startPolling() {
        stopPolling();
        pollingTimer = setInterval(fetchLiveScore, POLLING_INTERVAL);
    }

    function stopPolling() {
        if (pollingTimer) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
    }

    function loadSchools(branchId) {
        $schoolSelect.prop('disabled', true).html('<option value="">Memuat sekolah...</option>');
        if (!branchId) {
            $schoolSelect.html('<option value="">Semua Sekolah</option>').prop('disabled', false);
            return;
        }

        const url = routes.schoolsByBranch.replace('__BRANCH__', branchId);
        $.getJSON(url)
            .done(function (schools) {
                const options = schools.map(function (school) {
                    return `<option value="${school.id}">${school.school_name}</option>`;
                });
                $schoolSelect.html('<option value="">Semua Sekolah</option>' + options.join('')).prop('disabled', false);
            })
            .fail(function () {
                $schoolSelect.html('<option value="">Gagal memuat sekolah</option>').prop('disabled', false);
            });
    }

    $(document).ready(function () {
        moment.locale('id');

        $branchSelect.on('change', function () {
            loadSchools($(this).val());
            fetchLiveScore();
        });

        $schoolSelect.on('change', fetchLiveScore);
        $statusSelect.on('change', fetchLiveScore);
        $refreshButton.on('click', fetchLiveScore);

        fetchLiveScore();
        startPolling();

        $(window).on('beforeunload', stopPolling);
    });
</script>
@endpush
