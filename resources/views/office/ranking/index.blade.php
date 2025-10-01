@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow-sm border-0 ranking-card">
            <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between bg-primary text-white">
                <div>
                    <h4 class="mb-1"><i class="fas fa-trophy"></i> Perengkingan Hasil Ujian</h4>
                    <p class="mb-0 text-light">Pantau performa peserta ujian dan unduh laporan dalam format PDF.</p>
                </div>
                <div class="mt-3 mt-lg-0">
                    <button id="download-pdf" class="btn btn-danger btn-sm mr-2" disabled>
                        <i class="fas fa-file-pdf"></i> Unduh PDF
                    </button>
                    <button id="refresh-btn" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row gutter-sm">
                    <div class="form-group col-md-4">
                        <label for="filter_exam">Pilih Ujian</label>
                        <select id="filter_exam" class="form-control custom-select">
                            <option value="">-- Pilih Ujian --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->exam_name }} ({{ $exam->exam_code }})</option>
                            @endforeach
                        </select>
                    </div>
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
                </div>
                <div class="row gutter-sm">
                    <div class="form-group col-md-4">
                        <label>Jenis Peserta</label>
                        <div class="btn-group btn-group-sm btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-primary active" data-type="student">
                                <input type="radio" name="participant_type" value="student" autocomplete="off" checked> Siswa
                            </label>
                            <label class="btn btn-outline-primary" data-type="teacher">
                                <input type="radio" name="participant_type" value="teacher" autocomplete="off"> Guru/Staff
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="filter_limit">Jumlah Data</label>
                        <select id="filter_limit" class="form-control custom-select">
                            <option value="50">Top 50</option>
                            <option value="100" selected>Top 100</option>
                            <option value="150">Top 150</option>
                            <option value="200">Top 200</option>
                            <option value="300">Top 300</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <div class="w-100 text-muted small" id="last-updated">Silakan pilih ujian terlebih dahulu.</div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="ranking-stat">
                            <div class="stat-label">Total Peserta</div>
                            <div class="stat-value" id="summary-total">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ranking-stat">
                            <div class="stat-label">Nilai Tertinggi</div>
                            <div class="stat-value" id="summary-best">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ranking-stat">
                            <div class="stat-label">Rata-rata Nilai</div>
                            <div class="stat-value" id="summary-average">-</div>
                        </div>
                    </div>
                </div>

                <div class="top-three card mt-4">
                    <div class="card-header py-2 bg-light">
                        <h6 class="mb-0 text-primary"><i class="fas fa-medal"></i> Peringkat Teratas</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="top-three-list">
                            <li class="list-group-item text-muted text-center">Belum ada data peringkat.</li>
                        </ul>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-hover table-striped ranking-table" id="ranking-table">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 60px;">Peringkat</th>
                                <th>Peserta</th>
                                <th>Sekolah</th>
                                <th>Cabdin</th>
                                <th class="text-right">Nilai</th>
                                <th class="text-center">Benar</th>
                                <th class="text-center">Persentase</th>
                                <th class="text-center">Durasi</th>
                                <th class="text-center">Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Pilih filter untuk menampilkan data perengkingan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form id="pdf-form" action="{{ route('ranking.download') }}" method="GET" target="_blank" class="d-none">
            <input type="hidden" name="exam_id">
            <input type="hidden" name="branch_id">
            <input type="hidden" name="school_id">
            <input type="hidden" name="participant_type">
            <input type="hidden" name="limit">
        </form>
    </div>
@endsection

@push('styles')
<style>
    .ranking-card .card-header {
        border-bottom: none;
        border-top-left-radius: .5rem;
        border-top-right-radius: .5rem;
    }
    .ranking-stat {
        background: linear-gradient(135deg, #ecf5ff, #ffffff);
        border: 1px solid #d7e3ff;
        border-radius: 12px;
        padding: 16px 18px;
        box-shadow: 0 4px 12px rgba(15, 54, 108, 0.08);
        transition: transform 0.2s ease;
    }
    .ranking-stat:hover {
        transform: translateY(-2px);
    }
    .stat-label {
        font-size: 12px;
        color: #5f6b7c;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 6px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1f2d3d;
    }
    .ranking-table tbody tr.rank-top-1 td {
        background: linear-gradient(90deg, rgba(255, 223, 128, 0.28), rgba(255, 255, 255, 0.4));
    }
    .ranking-table tbody tr.rank-top-2 td {
        background: linear-gradient(90deg, rgba(186, 208, 224, 0.3), rgba(255, 255, 255, 0.4));
    }
    .ranking-table tbody tr.rank-top-3 td {
        background: linear-gradient(90deg, rgba(215, 180, 158, 0.25), rgba(255, 255, 255, 0.4));
    }
    .ranking-table tbody tr td {
        vertical-align: middle;
    }
    .badge-soft {
        background: rgba(48, 97, 190, 0.12);
        color: #305fbe;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    .list-group-item {
        font-size: 13px;
    }
    .top-three .list-group-item strong {
        color: #20304d;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/id.min.js"></script>
<script>
    moment.locale('id');

    const routes = {
        schoolsByBranch: @json(route('pegawai.getByBranch', ['branchId' => '__BRANCH__'])),
        rankingData: @json(route('ranking.data')),
    };

    const $examSelect = $('#filter_exam');
    const $branchSelect = $('#filter_branch');
    const $schoolSelect = $('#filter_school');
    const $limitSelect = $('#filter_limit');
    const $participantRadios = $('input[name="participant_type"]');
    const $tableBody = $('#ranking-table tbody');
    const $lastUpdated = $('#last-updated');
    const $summaryTotal = $('#summary-total');
    const $summaryBest = $('#summary-best');
    const $summaryAverage = $('#summary-average');
    const $topThreeList = $('#top-three-list');
    const $downloadBtn = $('#download-pdf');
    const $refreshBtn = $('#refresh-btn');
    const $pdfForm = $('#pdf-form');

    function escapeHtml(value) {
        return $('<div>').text(value != null ? value : '').html();
    }

    function getParticipantType() {
        return $('input[name="participant_type"]:checked').val() || 'student';
    }

    function setLoading() {
        $tableBody.html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data perengkingan...</td></tr>');
    }

    function resetSummary() {
        $summaryTotal.text('-');
        $summaryBest.text('-');
        $summaryAverage.text('-');
        $topThreeList.html('<li class="list-group-item text-muted text-center">Belum ada data peringkat.</li>');
        $downloadBtn.prop('disabled', true);
    }

    function renderTopThree(data) {
        if (!data || data.length === 0) {
            $topThreeList.html('<li class="list-group-item text-muted text-center">Belum ada data peringkat.</li>');
            return;
        }

        const topThree = data.slice(0, 3).map(function (item, index) {
            const badges = [
                '<span class="badge badge-warning mr-2"><i class="fas fa-crown"></i></span>',
                '<span class="badge badge-secondary mr-2"><i class="fas fa-medal"></i></span>',
                '<span class="badge badge-info mr-2"><i class="fas fa-award"></i></span>'
            ];
            const indicator = badges[index] || '<span class="badge badge-light mr-2">#' + (index + 1) + '</span>';
            return `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        ${indicator}<strong>${escapeHtml(item.participant_name)}</strong>
                        <div class="small text-muted">${escapeHtml(item.school_name)} &bull; Nilai ${escapeHtml(item.score_formatted)}</div>
                    </div>
                    <span class="badge-soft">${escapeHtml(item.participant_label)}</span>
                </li>
            `;
        });

        $topThreeList.html(topThree.join(''));
    }

    function buildRow(item) {
        const rowClass = item.rank <= 3 ? 'rank-top-' + item.rank : '';
        const correctInfo = item.correct_questions != null && item.total_questions != null
            ? `${item.correct_questions} / ${item.total_questions}`
            : '-';
        const percentage = item.percentage_formatted || '-';
        const duration = item.duration_formatted || '-';
        const submitted = item.submitted_at_formatted || '-';
        const meta = item.participant_meta ? `<span class="badge-soft">${escapeHtml(item.participant_meta)}</span>` : '';

        return `
            <tr class="${rowClass}">
                <td>
                    <div class="font-weight-bold mb-1">#${item.rank}</div>
                    <div class="text-muted small">Sesi ${item.session_number || '-'}</div>
                </td>
                <td>
                    <div class="font-weight-bold">${escapeHtml(item.participant_name)}</div>
                    <div class="text-muted small">${escapeHtml(item.participant_label)} | ${escapeHtml(item.participant_identifier)}</div>
                    <div class="mt-1">${meta}</div>
                </td>
                <td>${escapeHtml(item.school_name)}</td>
                <td>${escapeHtml(item.branch_name)}</td>
                <td class="text-right"><strong>${escapeHtml(item.score_formatted)}</strong></td>
                <td class="text-center">${correctInfo}</td>
                <td class="text-center">${percentage}</td>
                <td class="text-center">${duration}</td>
                <td class="text-center">${submitted}</td>
            </tr>
        `;
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            $tableBody.html('<tr><td colspan="9" class="text-center text-muted py-4">Data ranking tidak ditemukan untuk filter saat ini.</td></tr>');
            return;
        }

        const rows = data.map(buildRow).join('');
        $tableBody.html(rows);
    }

    function updateSummary(summary, data) {
        if (!summary) {
            resetSummary();
            return;
        }
        $summaryTotal.text(summary.total != null ? summary.total : '-');
        $summaryBest.text(summary.best_score != null ? parseFloat(summary.best_score).toFixed(2) : '-');
        $summaryAverage.text(summary.average_score != null ? parseFloat(summary.average_score).toFixed(2) : '-');
        $downloadBtn.prop('disabled', !data || data.length === 0);
    }

    function loadSchools(branchId) {
        if (!branchId) {
            $schoolSelect.prop('disabled', false).html('<option value="">Semua Sekolah</option>');
            return;
        }

        $schoolSelect.prop('disabled', true).html('<option value="">Memuat sekolah...</option>');
        const url = routes.schoolsByBranch.replace('__BRANCH__', branchId);
        $.getJSON(url)
            .done(function (schools) {
                const options = schools.map(function (school) {
                    return `<option value="${school.id}">${escapeHtml(school.school_name)}</option>`;
                });
                $schoolSelect.html('<option value="">Semua Sekolah</option>' + options.join('')).prop('disabled', false);
            })
            .fail(function () {
                $schoolSelect.html('<option value="">Gagal memuat sekolah</option>').prop('disabled', false);
            });
    }

    function buildParams() {
        return {
            exam_id: $examSelect.val() || '',
            branch_id: $branchSelect.val() || '',
            school_id: $schoolSelect.val() || '',
            participant_type: getParticipantType(),
            limit: $limitSelect.val() || '',
        };
    }

    function fetchRanking() {
        const params = buildParams();
        if (!params.exam_id) {
            resetSummary();
            $tableBody.html('<tr><td colspan="9" class="text-center text-muted py-4">Silakan pilih ujian untuk melihat perengkingan.</td></tr>');
            $lastUpdated.text('Ujian belum dipilih.');
            return;
        }

        setLoading();
        $.getJSON(routes.rankingData, params)
            .done(function (response) {
                const data = response.data || [];
                renderTable(data);
                renderTopThree(data);
                updateSummary(response.summary, data);
                const fetchedAt = moment().format('DD MMM YYYY HH:mm:ss');
                $lastUpdated.text('Terakhir diperbarui: ' + fetchedAt + ' WIB');
            })
            .fail(function () {
                resetSummary();
                $tableBody.html('<tr><td colspan="9" class="text-center text-danger py-4">Terjadi kesalahan saat memuat data ranking.</td></tr>');
                $lastUpdated.text('Gagal memuat data. Coba ulangi.');
            });
    }

    function submitPdf() {
        const params = buildParams();
        if (!params.exam_id) {
            alert('Silakan pilih ujian terlebih dahulu sebelum mengunduh PDF.');
            return;
        }
        Object.keys(params).forEach(function (key) {
            $pdfForm.find('[name="' + key + '"]').val(params[key]);
        });
        $pdfForm.trigger('submit');
    }

    $(document).ready(function () {
        $examSelect.on('change', function () {
            fetchRanking();
        });

        $branchSelect.on('change', function () {
            const branchId = $(this).val();
            loadSchools(branchId);
            fetchRanking();
        });

        $schoolSelect.on('change', fetchRanking);
        $limitSelect.on('change', fetchRanking);
        $participantRadios.on('change', function () {
            $(this).closest('label').addClass('active').siblings().removeClass('active');
            fetchRanking();
        });
        $refreshBtn.on('click', fetchRanking);
        $downloadBtn.on('click', submitPdf);

        // Reset summary on initial load
        resetSummary();
    });
</script>
@endpush
