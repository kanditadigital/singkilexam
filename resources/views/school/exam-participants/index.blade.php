@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card mb-4 bg-secondary text-white">
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
                    <div class="form-group col-md-4">
                        <label for="participant_type">Jenis Peserta</label>
                        <select id="participant_type" class="form-control custom-select">
                            <option value="student">Siswa</option>
                            <option value="employee">Guru & Staff</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4" id="gender-filter-group">
                        <label for="filter_gender">Filter Jenis Kelamin</label>
                        <select id="filter_gender" class="form-control custom-select">
                            <option value="">Semua</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Participants Card -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-fw fa-th-list"></i> Data Peserta Tersedia</h4>
                <button id="btn-save-participants" class="btn btn-reka btn-sm" disabled>
                    <i class="fas fa-plus"></i> Tambah Peserta Terpilih
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm mb-0" id="students-table">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%" class="text-center">
                                    <input type="checkbox" id="select-all-participants" class="mt-1">
                                </th>
                                <th>Nama</th>
                                <th>Identitas</th>
                                <th>Info</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Registered Participants Card -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-fw fa-th-list"></i> Peserta Terdaftar</h4>
                <button id="btn-bulk-delete" class="btn btn-reka btn-sm" disabled>
                    <i class="fas fa-trash"></i> Hapus Terpilih
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm mb-0" id="participants-table">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%" class="text-center">
                                    <input type="checkbox" id="select-all-registered" class="mt-1">
                                </th>
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
    const destroyTemplate = @json(route('sch.exam-participants.destroy', ['participant' => ':id']));
    const printCardsTemplate = @json(route('sch.exam-participants.print-cards', ['exam' => ':exam']));
    const routes = {
        students: @json(route('sch.exam-participants.students')),
        registered: @json(route('sch.exam-participants.registered')),
        store: @json(route('sch.exam-participants.store')),
        destroy: (id) => destroyTemplate.replace(':id', id),
        bulkDestroy: @json(route('sch.exam-participants.bulk-destroy')),
        printCards: (examId) => printCardsTemplate.replace(':exam', examId)
    };

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const $examSelect = $('#exam_id');
    const $participantType = $('#participant_type');
    const $genderFilter = $('#filter_gender');
    const $genderGroup = $('#gender-filter-group');
    const $saveButton = $('#btn-save-participants');
    const $printCardsButton = $('#btn-print-cards');
    const $bulkDeleteButton = $('#btn-bulk-delete');
    const $helper = $('#exam-helper');
    const $selectAllCheckbox = $('#select-all-participants');
    const $selectAllRegisteredCheckbox = $('#select-all-registered');

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

    function selectedRegisteredIds() {
        const ids = [];
        $('#participants-table').find('input.registered-select:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function enableSaveButton() {
        $saveButton.prop('disabled', selectedParticipantIds().length === 0);
    }

    function enableBulkDeleteButton() {
        $bulkDeleteButton.prop('disabled', selectedRegisteredIds().length === 0);
    }

    function updateSelectAllState() {
        const totalCheckboxes = $('#students-table tbody input.participant-select').length;
        const checkedCheckboxes = $('#students-table tbody input.participant-select:checked').length;
        $selectAllCheckbox.prop('checked', checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0);
        $selectAllCheckbox.prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
    }

    function updateSelectAllRegisteredState() {
        const totalCheckboxes = $('#participants-table tbody input.registered-select').length;
        const checkedCheckboxes = $('#participants-table tbody input.registered-select:checked').length;
        $selectAllRegisteredCheckbox.prop('checked', checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0);
        $selectAllRegisteredCheckbox.prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
    }

    function enablePrintButtons() {
        const examId = currentExamId();
        $printCardsButton.prop('disabled', !examId);
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
                updateSelectAllState();
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
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'type_label', name: 'type_label' },
                { data: 'meta', name: 'meta' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            drawCallback: function () {
                updateSelectAllRegisteredState();
                enableBulkDeleteButton();
            }
        });
    }

    function refreshTables() {
        const examId = currentExamId();
        if (!examId) {
            $helper.show();
            $saveButton.prop('disabled', true);
            $bulkDeleteButton.prop('disabled', true);
            enablePrintButtons();
            if (studentsTable) studentsTable.clear().draw();
            if (participantsTable) participantsTable.clear().draw();
            return;
        }

        $helper.hide();
        enablePrintButtons();

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
            updateSelectAllState();
        });

        $('#participants-table').on('change', 'input.registered-select', function () {
            enableBulkDeleteButton();
            updateSelectAllRegisteredState();
        });

        $selectAllCheckbox.on('change', function () {
            const isChecked = $(this).is(':checked');
            $('#students-table tbody input.participant-select').prop('checked', isChecked);
            enableSaveButton();
            updateSelectAllState();
        });

        $selectAllRegisteredCheckbox.on('change', function () {
            const isChecked = $(this).is(':checked');
            $('#participants-table tbody input.registered-select').prop('checked', isChecked);
            enableBulkDeleteButton();
            updateSelectAllRegisteredState();
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

        $printCardsButton.on('click', function () {
            const examId = currentExamId();
            if (!examId) {
                Swal.fire('Informasi', 'Pilih ujian terlebih dahulu.', 'info');
                return;
            }
            window.open(routes.printCards(examId), '_blank');
        });

        $bulkDeleteButton.on('click', function () {
            const ids = selectedRegisteredIds();

            if (ids.length === 0) {
                Swal.fire('Informasi', 'Pilih minimal satu peserta untuk dihapus.', 'info');
                return;
            }

            Swal.fire({
                title: 'Hapus Peserta Terpilih?',
                text: `Anda akan menghapus ${ids.length} peserta dari ujian ini.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: routes.bulkDestroy,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: {
                        participant_ids: ids
                    }
                }).done(function (response) {
                    Swal.fire('Berhasil', response.message || 'Peserta berhasil dihapus.', 'success');
                    if (participantsTable) participantsTable.ajax.reload();
                    if (studentsTable) studentsTable.ajax.reload(null, false);
                }).fail(function (xhr) {
                    const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus peserta.';
                    Swal.fire('Gagal', message, 'error');
                });
            });
        });
    });
</script>
@endpush
