@extends('layouts.main')

@section('content')
<div class="section-body">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white py-2">
            <h4 class="mb-0"><i class="fas fa-eye"></i> Monitoring Ujian</h4>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="filter_status" class="font-weight-bold">Status:</label>
                    <select id="filter_status" class="form-control custom-select form-control-sm">
                        <option value="">-- Semua --</option>
                        <option value="ongoing">Sedang Mengerjakan</option>
                        <option value="submitted">Sudah Submit</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm" id="datayajra">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>No.</th>
                            <th>Nama Peserta</th>
                            <th>Mata Pelajaran</th>
                            <th>Waktu Mulai</th>
                            <th>Lama Mengerjakan</th>
                            <th>Waktu Selesai</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#datayajra').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `{{ route('sch.exam-monitoring.index') }}`,
            type: 'GET',
            data: function(d) {
                d.status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'participant_name', name: 'participant_name' },
            { data: 'subject_name', name: 'subject_name' },
            { data: 'started_at_formatted', name: 'started_at_formatted', className: 'text-center' },
            { data: 'duration', name: 'duration', className: 'text-center' },
            { data: 'submitted_at_formatted', name: 'submitted_at_formatted', className: 'text-center' }
        ]
    });

    // Filter event handlers
    $('#filter_status').change(function(){
        table.ajax.reload();
    });
});
</script>
@endpush
