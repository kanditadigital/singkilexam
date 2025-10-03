@extends('layouts.main')

@section('content')
    <div class="section-body">
        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-users"></i> Data Guru & Staff</h4>
                <div class="ml-auto">
                    <a href="{{ route('sch.employee.create') }}" class="btn btn-reka"><i class="fas fa-plus"></i> Tambah</a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm" id="datayajra">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tipe</th>
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
                url: `{{ route('sch.employee.index') }}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'employee_name', name: 'employee_name' },
                { data: 'username', name: 'username' },
                { data: 'email', name: 'email' },
                { data: 'employee_type', name: 'employee_type' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '20%', className: 'text-center' },
            ]
        });

        $('#datayajra').on('click', '.edit', function() {
            const id = $(this).data('id');
            window.location.href = `{{ url('sch/employee') }}/${id}edit`;
        });

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
                    deletePegawai(id);
                }
            });
        }

        function deletePegawai(id) {
            $.ajax({
                url: `{{ url('sch/employee') }}/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function() {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Data telah dihapus.',
                        icon: 'success',
                        timer: 2000,
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
@endpush
