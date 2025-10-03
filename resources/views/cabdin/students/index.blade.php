@extends('layouts.main')

@section('content')
<div class="section-body">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-user-graduate"></i> Data Siswa</h4>
            <a href="{{ route('cabdin.students.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <div class="card-body">
            <!-- Filter sekolah -->
            <div class="mb-3">
                <label for="filter_school" class="font-weight-bold">Filter Sekolah:</label>
                <select id="filter_school" class="form-control custom-select form-control-sm w-100">
                    <option value="">-- Semua Sekolah --</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm" id="datayajra">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>No.</th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>Sekolah</th>
                            <th>Jenis Kelamin</th>
                            <th>Opsi</th>
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
            url: `{{ route('cabdin.students.index') }}`,
            type: 'GET',
            data: function(d) {
                d.school_id = $('#filter_school').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'student_nisn', name: 'student_nisn' },
            { data: 'school_name', name: 'school_name' },
            { data: 'gender', name: 'gender', orderable: false, searchable: false, className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
    });

    // filter sekolah
    $('#filter_school').change(function(){
        table.ajax.reload();
    });

    // Edit
    $('#datayajra').on('click', '.edit', function() {
        const id = $(this).data('id');
        window.location.href = `{{ url('cabdin/students') }}/${id}/edit`;
    });

    // Delete
    $('#datayajra').on('click', '.delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        confirmDelete(id);
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus data?',
            text: 'Data siswa akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#113F67',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                deleteStudent(id);
            }
        });
    }

    function deleteStudent(id) {
        $.ajax({
            url: `{{ url('cabdin/students') }}/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(resp) {
                if (resp.ok) {
                    Swal.fire('Berhasil!', resp.message, 'success');
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    }

    // Reset Password
    $('#datayajra').on('click', '.reset', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        confirmResetPassword(id);
    });

    function confirmResetPassword(id) {
        Swal.fire({
            title: 'Reset Password?',
            text: 'Password siswa akan diganti dengan yang baru.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#113F67',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, reset!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                resetPassword(id);
            }
        });
    }

    function resetPassword(id) {
        $.ajax({
            url: `{{ route('cabdin.students.reset-password', ':id') }}`.replace(':id', id),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(resp) {
                if (resp.ok) {
                    Swal.fire({
                        title: 'Password Baru',
                        html: `
                            <div class="text-left">
                                <div><strong>Nama:</strong> ${resp.student.name}</div>
                                <div><strong>NISN:</strong> ${resp.student.nisn}</div>
                                <div><strong>Sekolah:</strong> ${resp.student.school}</div>
                                <div class="mt-2"><strong>Password:</strong> ${resp.password}</div>
                                <small class="text-muted d-block mt-2">Simpan password ini dan berikan ke siswa.</small>
                            </div>
                        `,
                        icon: 'success'
                    });
                } else {
                    Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
            }
        });
    }
});
</script>
@endpush
