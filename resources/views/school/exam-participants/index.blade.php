@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="alert alert-danger" id="exam-helper" {{ $selectedExamId ? 'style=display:none;' : '' }}>
            Silakan pilih ujian terlebih dahulu untuk menambah atau melihat peserta.
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-user-check"></i> Kelola Peserta Ujian</h4>
            </div>
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
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
                    <div class="form-group col-md-3">
                        <label for="participant_type">Jenis Peserta</label>
                        <select id="participant_type" class="form-control custom-select">
                            <option value="student">Siswa</option>
                            <option value="employee">Guru & Staff</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3" id="gender-filter-group">
                        <label for="filter_gender">Filter Jenis Kelamin</label>
                        <select id="filter_gender" class="form-control custom-select">
                            <option value="">Semua</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2 text-right">
                        <button id="btn-save-participants" class="btn btn-primary btn-block" disabled>
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border">
                            <div class="card-header bg-primary text-white py-1">
                                <h4><i class="fas fa-users"></i> Daftar Peserta Tersedia</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0" id="students-table">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Pilih</th>
                                                <th>Nama</th>
                                                <th>Identitas</th>
                                                <th>Info</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card border">
                            <div class="card-header bg-primary text-dark py-1">
                                <h4><i class="fas fa-list"></i> Peserta Terdaftar</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0" id="participants-table">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th>Nama</th>
                                                <th>Jenis Peserta</th>
                                                <th>Info</th>
                                                <th width="20%">Aksi</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <small class="text-muted">Catatan: Peserta yang sudah terdaftar ditandai dengan badge hijau dan tidak dapat dipilih ulang. Gunakan tabel "Peserta Terdaftar" untuk menghapus peserta.</small>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    #students-table tbody tr td,
    #participants-table tbody tr td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
    const destroyTemplate = @json(route('exam-participants.destroy', ['participant' => ':id']));
    const routes = {
        students: @json(route('exam-participants.students')),
        registered: @json(route('exam-participants.registered')),
        store: @json(route('exam-participants.store')),
        destroy: (id) => destroyTemplate.replace(':id', id)
    };

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const $examSelect = $('#exam_id');
    const $participantType = $('#participant_type');
    const $genderFilter = $('#filter_gender');
    const $genderGroup = $('#gender-filter-group');
    const $saveButton = $('#btn-save-participants');
    const $helper = $('#exam-helper');

    let studentsTable = null;
    let participantsTable = null;

    function currentExamId() {
        return $examSelect.val();
    }

    function currentParticipantType() {
        return $participantType.val() || 'student';
    }

    function selectedParticipantIds() {
        const ids = [];
        $('#students-table').find('input.participant-select:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function enableSaveButton() {
        $saveButton.prop('disabled', selectedParticipantIds().length === 0);
    }

    function buildStudentsTable() {
        if (studentsTable) {
            studentsTable.ajax.reload();
            return;
        }

        studentsTable = $('#students-table').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: routes.students,
                data: function (params) {
                    params.exam_id = currentExamId();
                    params.type = currentParticipantType();
                    params.gender = $genderFilter.val();
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'identifier', name: 'identifier' },
                { data: 'meta', name: 'meta' },
            ],
            columnDefs: [
                { targets: [0, 1], className: 'text-center align-middle' },
            ],
            drawCallback: function () {
                enableSaveButton();
            }
        });
    }

    function buildParticipantsTable() {
        if (participantsTable) {
            participantsTable.ajax.reload();
            return;
        }

        participantsTable = $('#participants-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: routes.registered,
                data: function (params) {
                    params.exam_id = currentExamId();
                    params.type = currentParticipantType();
                },
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'type_label', name: 'type_label' },
                { data: 'meta', name: 'meta' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    }

    function refreshTables() {
        const examId = currentExamId();
        if (!examId) {
            $helper.show();
            $saveButton.prop('disabled', true);
            if (studentsTable) studentsTable.clear().draw();
            if (participantsTable) participantsTable.clear().draw();
            return;
        }

        $helper.hide();

        if (studentsTable) {
            studentsTable.ajax.reload();
        } else {
            buildStudentsTable();
        }

        if (participantsTable) {
            participantsTable.ajax.reload();
        } else {
            buildParticipantsTable();
        }
    }

    function toggleFilters() {
        if (currentParticipantType() === 'employee') {
            $genderFilter.val('');
            $genderGroup.addClass('d-none');
        } else {
            $genderGroup.removeClass('d-none');
        }
    }

    $(document).ready(function () {
        toggleFilters();

        if (currentExamId()) {
            refreshTables();
        }

        $examSelect.on('change', function () {
            refreshTables();
        });

        $participantType.on('change', function () {
            toggleFilters();
            refreshTables();
        });

        $genderFilter.on('change', function () {
            if (studentsTable) {
                studentsTable.ajax.reload();
            }
        });

        $('#students-table').on('change', 'input.participant-select', function () {
            enableSaveButton();
        });

        $('#participants-table').on('click', '.remove-participant', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Peserta?',
                text: 'Peserta akan dihapus dari ujian ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#113F67',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: routes.destroy(id),
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                }).done(function () {
                    Swal.fire('Berhasil', 'Peserta telah dihapus.', 'success');
                    if (participantsTable) participantsTable.ajax.reload();
                    if (studentsTable) studentsTable.ajax.reload(null, false);
                }).fail(function () {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus peserta.', 'error');
                });
            });
        });

        $saveButton.on('click', function () {
            const examId = currentExamId();
            const ids = selectedParticipantIds();
            const type = currentParticipantType();

            if (!examId) {
                Swal.fire('Informasi', 'Pilih ujian terlebih dahulu.', 'info');
                return;
            }

            if (ids.length === 0) {
                Swal.fire('Informasi', 'Pilih minimal satu peserta untuk disimpan.', 'info');
                return;
            }

            $.ajax({
                url: routes.store,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: {
                    exam_id: examId,
                    participant_type: type,
                    participant_ids: ids
                }
            }).done(function (response) {
                Swal.fire('Berhasil', response.message || 'Peserta berhasil disimpan.', 'success');
                if (studentsTable) studentsTable.ajax.reload();
                if (participantsTable) participantsTable.ajax.reload();
            }).fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan peserta.';
                Swal.fire('Gagal', message, 'error');
            });
        });
    });
</script>
@endpush
