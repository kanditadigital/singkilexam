@extends('layouts.main')

@section('content')
<div class="section-body">
    <div class="card shadow">
        <!-- Header -->
        <div class="card-header bg-primary text-white py-1 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-fw fa-th-list"></i> Data Sekolah
            </h4>
            <a href="{{ route('cabdin.schools.create') }}" class="btn btn-reka btn-sm">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>

        <!-- Body -->
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped table-hover table-sm" id="datayajra">
                <thead class="thead-dark">
                    <tr class="text-center">
                        <th>No.</th>
                        <th>NPSN</th>
                        <th>Nama Sekolah</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
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
$(function () {
    const table = $('#datayajra').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `{{ route('cabdin.schools.index') }}`,
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'school_npsn', name: 'school_npsn' },
            { data: 'school_name', name: 'school_name' },
            { data: 'school_phone', name: 'school_phone' },
            {
                data: 'is_active',
                name: 'is_active',
                render: data => data
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-danger">Nonaktif</span>',
                className: 'text-center'
            },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
    });

    /* =====================
     * EVENT HANDLER
     * ===================== */
    // Edit data
    $('#datayajra').on('click', '.edit', function() {
        const id = $(this).data('id');
        window.location.href = `{{ url('cabdin/schools') }}/${id}/edit`;
    });

    // Delete data
    $('#datayajra').on('click', '.delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        confirmDelete(id);
    });

    // Reset password
    $('#datayajra').on('click', '.reset', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        confirmResetPassword(id);
    });

    /* =====================
     * FUNCTIONS
     * ===================== */
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
                deleteSekolah(id);
            }
        });
    }

    function deleteSekolah(id) {
        $.ajax({
            url: `{{ url('cabdin/schools') }}/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
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

    function confirmResetPassword(id) {
        Swal.fire({
            title: 'Reset Password?',
            text: 'Password akan direset ke nilai baru.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#113F67',
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
            url: `{{ route('cabdin.schools.reset', ':id') }}`.replace(':id', id),
            type: 'POST',
            dataType: 'json',
            data: { _token: '{{ csrf_token() }}' },
            success: function(resp) {
                if (resp?.ok) {
                    Swal.fire({
                        title: `${escapeHtml(resp.school.name)}`,
                        html: `
                            <div class="text-left">
                                <div class="input-group mt-1">
                                    <input id="pwd-new" type="text" class="form-control" readonly value="${escapeHtml(resp.password)}">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" onclick="copyPwd()">Copy</button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">Simpan password ini dan berikan ke pihak sekolah.</small>
                            </div>
                        `,
                        icon: 'success',
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire('Gagal!', resp?.message || 'Terjadi kesalahan.', 'error');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat reset password.';
                Swal.fire('Gagal!', msg, 'error');
            }
        });
    }

    // helper aman untuk HTML
    function escapeHtml(str) {
        return String(str).replace(/[&<>"'`=\/]/g, s => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',
            '`':'&#x60;','=':'&#x3D;','/':'&#x2F;'
        }[s]));
    }

    // copy password dengan API modern
    window.copyPwd = function() {
        const pwd = document.getElementById('pwd-new').value;
        navigator.clipboard.writeText(pwd).then(() => {
            Swal.fire({
                toast:true,
                position:'top-end',
                timer:1500,
                showConfirmButton:false,
                icon:'success',
                title:'Password disalin'
            });
        });
    };
});
</script>
@endpush
