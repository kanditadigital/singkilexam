@extends('layouts.main')

@section('content')
<div class="section-body">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white py-2">
            <h4 class="mb-0"><i class="fas fa-user-check"></i> Data Peserta Ujian</h4>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="filter_school" class="font-weight-bold">Filter Sekolah:</label>
                    <select id="filter_school" class="form-control custom-select form-control-sm">
                        <option value="">-- Semua Sekolah --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="filter_subject" class="font-weight-bold">Filter Mata Pelajaran:</label>
                    <select id="filter_subject" class="form-control custom-select form-control-sm">
                        <option value="">-- Semua Mata Pelajaran --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm" id="datayajra">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>No.</th>
                            <th>Nama Peserta</th>
                            <th>Nama Sekolah</th>
                            <th>Mata Pelajaran</th>
                            <th>Nama Ujian</th>
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
            url: `{{ route('cabdin.exam-participants.index') }}`,
            type: 'GET',
            data: function(d) {
                d.school_id = $('#filter_school').val();
                d.subject_id = $('#filter_subject').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'participant_name', name: 'participant_name' },
            { data: 'school_name', name: 'school_name' },
            { data: 'subjects', name: 'subjects' },
            { data: 'exam_name', name: 'exam_name' }
        ]
    });

    // Filter event handlers
    $('#filter_school, #filter_subject').change(function(){
        table.ajax.reload();
    });
});
</script>
@endpush
