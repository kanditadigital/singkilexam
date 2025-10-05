@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-th-list"></i> Data Siswa</h4>
                <div class="ml-auto">
                    <button type="button" class="btn btn-reka mr-2" data-toggle="modal" data-target="#importModal">
                        <i class="fas fa-upload"></i> Import Excel
                    </button>
                    <a href="{{ route('sch.student.create') }}" class="btn btn-reka"><i class="fas fa-plus"></i> Tambah</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm" id="datayajra">
                    <thead class="thead-dark">
                        <tr>
                            <th>No.</th>
                            <th>Nama Siswa</th>
                            <th>Username</th>
                            <th>Jenis Kelamin</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#datayajra').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: `{{ route('sch.student.index') }}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'student_name', name: 'student_name' },
                { data: 'username', name: 'username' },
                { data: 'student_gender', name: 'student_gender' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '25%', className: 'text-center' },
            ]
        });

        $('#datayajra').on('click', '.edit', function() {
            const id = $(this).data('id');
            window.location.href = `{{ url('sch/student') }}/${id}/edit`;
        });

        // Reset password dengan konfirmasi SweetAlert
        $('#datayajra').on('click', '.reset-password', function(event) {
            event.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name');
            confirmResetPassword(id, name);
        });

        // Hapus data dengan konfirmasi SweetAlert
        $('#datayajra').on('click', '.delete', function(event) {
            event.preventDefault();
            const id = $(this).data('id');
            confirmDelete(id);
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#113F67',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteSiswa(id);
                }
            });
        }

        function confirmResetPassword(id, name) {
            Swal.fire({
                title: 'Reset Password?',
                text: `Yakin ingin mereset password ${name}? Password baru akan dibuat otomatis.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, reset!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    resetPassword(id);
                }
            });
        }

        function resetPassword(id) {
            $.ajax({
                url: `{{ url('sch/student') }}/${id}/reset-password`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: `Password berhasil direset. Password baru: ${response.new_password}`,
                        icon: 'success',
                        timer: 5000,
                        showConfirmButton: true
                    });
                },
                error: function() {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat mereset password.', 'error');
                }
            });
        }

        function deleteSiswa(id) {
            $.ajax({
                url: `{{ url('sch/student') }}/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Data telah dihapus.',
                        icon: 'success',
                        timer: 2000, // 2 detik
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                    table.ajax.reload();
                },
                error: function() {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
                }
            });
        }

    });
</script>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title" id="importModalLabel">Import Data Siswa</h5>
                <a href="#importModal" class="text-white" data-dismiss="modal"><i class="fas fa-fw fa-times"></i></a>
            </div>
            <form action="{{ route('sch.student.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pb-0">
                    <div class="form-group">
                        <label for="file">Pilih File Excel (.xlsx atau .xls)</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                    </div>
                    <small class="form-text text-muted">
                        Format kolom: nama_siswa, nisn, jenis_kelamin (L/P)
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-fw fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
