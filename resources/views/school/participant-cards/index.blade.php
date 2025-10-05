@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-2">
                <h4><i class="fas fa-fw fa-th-list"></i> Cetak Kartu Peserta</h4>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="form-row align-items-end mb-4">
                    <div class="form-group col-md-5">
                        <label for="exam_id">Pilih Ujian <span class="text-danger">*</span></label>
                        <select id="exam_id" class="form-control custom-select">
                            <option value="">-- Pilih Ujian --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }} ({{ $exam->exam_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="participant_type">Filter Jenis Peserta</label>
                        <select id="participant_type" class="form-control custom-select">
                            <option value="">Semua Peserta</option>
                            <option value="student" {{ $selectedType == 'student' ? 'selected' : '' }}>Siswa</option>
                            <option value="employee" {{ $selectedType == 'employee' ? 'selected' : '' }}>Guru & Staff</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <button id="btn-preview" class="btn btn-primary btn-block" disabled>
                            <i class="fas fa-search-plus"></i> Pratinjau
                        </button>
                    </div>
                </div>

                <!-- Preview Area -->
                <div id="preview-container" class="d-none">
                    <div class="card border shadow-sm">
                        <div class="card-body">
                            <div id="preview-content" class="border" style="min-height: 600px; background: #f8f9fa;">
                                <div class="text-center text-muted" id="preview-placeholder">
                                    <i class="fas fa-file-pdf fa-3x mb-3"></i>
                                    <p>Klik tombol "Pratinjau" untuk melihat kartu peserta</p>
                                </div>
                                <iframe id="pdf-preview" class="d-none w-100" style="height: 580px; border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    #pdf-preview {
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    const routes = {
        preview: @json(route('sch.participant-cards.preview')),
        download: @json(route('sch.participant-cards.download'))
    };

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const $examSelect = $('#exam_id');
    const $typeSelect = $('#participant_type');
    const $previewBtn = $('#btn-preview');
    const $downloadBtn = $('#btn-download');
    const $previewContainer = $('#preview-container');
    const $previewContent = $('#preview-content');
    const $participantCount = $('#participant-count');

    function enableButtons() {
        const examId = $examSelect.val();
        $previewBtn.prop('disabled', !examId);
        $downloadBtn.prop('disabled', !examId);
    }

    $(document).ready(function () {
        enableButtons();

        $examSelect.on('change', function () {
            enableButtons();
            $previewContainer.addClass('d-none');
            $('#pdf-preview').addClass('d-none');
            $('#preview-placeholder').removeClass('d-none');
        });

        $typeSelect.on('change', function () {
            if ($examSelect.val()) {
                $previewContainer.addClass('d-none');
                $('#pdf-preview').addClass('d-none');
                $('#preview-placeholder').removeClass('d-none');
            }
        });

        $previewBtn.on('click', function () {
            const examId = $examSelect.val();
            const type = $typeSelect.val();

            if (!examId) {
                Swal.fire('Informasi', 'Pilih ujian terlebih dahulu.', 'info');
                return;
            }

            $previewBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memuat...');

            $.ajax({
                url: routes.preview,
                type: 'GET',
                data: {
                    exam_id: examId,
                    type: type
                }
            }).done(function (response) {
                $('#pdf-preview').attr('src', response.pdf_url);
                $('#pdf-preview').removeClass('d-none');
                $('#preview-placeholder').addClass('d-none');
                $participantCount.text(response.count);
                $previewContainer.removeClass('d-none');
            }).fail(function (xhr) {
                const message = xhr.responseJSON?.error || 'Terjadi kesalahan saat memuat pratinjau.';
                Swal.fire('Gagal', message, 'error');
            }).always(function () {
                $previewBtn.prop('disabled', false).html('<i class="fas fa-eye"></i> Pratinjau');
            });
        });

        $downloadBtn.on('click', function () {
            const examId = $examSelect.val();
            const type = $typeSelect.val();

            if (!examId) {
                Swal.fire('Informasi', 'Pilih ujian terlebih dahulu.', 'info');
                return;
            }

            $downloadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mendownload...');

            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = routes.download + '?exam_id=' + examId + '&type=' + type;
            link.download = 'kartu-peserta.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            setTimeout(function() {
                $downloadBtn.prop('disabled', false).html('<i class="fas fa-download"></i> Download PDF');
            }, 2000);
        });
    });
</script>
@endpush
