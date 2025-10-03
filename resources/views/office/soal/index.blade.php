@extends('layouts.main')

@section('content')
    <div class="section-body">

        <div class="card shadow">
            <div class="card-header bg-primary text-white py-1">
                <h4><i class="fas fa-fw fa-clipboard-list"></i> Data Soal &mdash; {{ optional($subject)->subject_name ?? '-' }}</h4>
                <div class="ml-auto">
                    @if(!empty($subjectId))
                        <a href="{{ route('disdik.soal.create', ['subject_id' => $subjectId]) }}" class="btn btn-reka"><i class="fas fa-plus"></i> Tambah</a>
                    @else
                        <button type="button" class="btn btn-secondary" disabled><i class="fas fa-plus"></i> Tambah</button>
                    @endif
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm" id="datayajra">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kategori Soal</th>
                            <th>Tipe Soal</th>
                            <th>Pertanyaan</th>
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
        const subjectId = @json($subjectId);

        if (subjectId) {
            localStorage.setItem('subject_id', subjectId);

            const table = $('#datayajra').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `{{ url('disdik/soal') }}`,
                    type: 'GET',
                    data: {
                        subject_id: subjectId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'question_category', name: 'question_category' },
                    { data: 'question_type', name: 'question_type' },
                    {
                        data: 'question_text',
                        name: 'question_text',
                        render: function(data) {
                            if (data) {
                                const text = data.replace(/<[^>]*>/g, '');
                                return text.length > 100 ? text.substring(0, 100) + '...' : text;
                            }
                            return '-';
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false, width: '20%', className: 'text-center' },
                ],
                language: {
                    processing: "Memuat data...",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            $('#datayajra').on('click', '.edit', function() {
                const id = $(this).data('id');
                window.location.href = `{{ url('disdik/soal') }}/${id}/edit`;
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
                        deleteSoal(id);
                    }
                });
            }

            function deleteSoal(id) {
                $.ajax({
                    url: `{{ url('disdik/soal') }}/${id}`,
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
                        $('#datayajra').DataTable().ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
                    }
                });
            }
        } else {
            $('#datayajra tbody').html('<tr><td colspan="6" class="text-center">Silakan pilih mata pelajaran terlebih dahulu.</td></tr>');
        }
    });
</script>
@endpush
