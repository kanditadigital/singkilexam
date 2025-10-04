@extends('site.main')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <div class="home-header d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-md-8">
                    <h3 class="hero-title">Computer Assissted Test (CAT)</h3>
                    <p class="hero-subtitle">Platform ujian online berbasis komputer untuk melaksanakan tryout TKA, ANBK dan Uji Kompetensi Guru.</p>
                </div>
                <div class="col-md-4">
                    <div class="card card-login shadow">
                        <div class="card-body">
                            <div class="my-4 text-center">
                                <h5>Member Area</h5>
                            </div>
                            <form action="{{ route('ayo.login') }}" method="POST">
                                @csrf
                                <div class="input-group flex-nowrap mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Email" autocomplete="off" required>
                                </div>
                                <div class="input-group mb-4">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="off" required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="d-flex align-items-center">
                                        <img id="captcha-img" src="{{ captcha_src('flat') }}" alt="captcha" style="width: 100%; height:auto;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="refresh-captcha">
                                            <i class="fa-solid fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-barcode"></i></span>
                                    <input type="text" class="form-control" name="captcha" placeholder="Kode Keamanan" required>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-cat w-100">Login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="statistic" class="statistics-section py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center mb-4">
                    <h2>Data Statistik</h2>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Cabang Dinas</h5>
                            <h3 class="text-primary">{{ $cabdinCount }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Sekolah</h5>
                            <h3 class="text-success">{{ $schoolCount }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Guru</h5>
                            <h3 class="text-warning">{{ $teacherCount }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Siswa</h5>
                            <h3 class="text-danger">{{ $studentCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white pt-3">
                            <h5>Grafik Statistik</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statisticsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white pt-3">
                            <h5>Data Statistik Hierarki</h5>
                        </div>
                        <div class="card-body">
                            <table id="statisticsTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Nama Cabang Dinas</th>
                                        <th>Jumlah Sekolah</th>
                                        <th>Jumlah Guru</th>
                                        <th>Jumlah Siswa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branches as $branch)
                                    <tr data-branch-id="{{ $branch->id }}">
                                        <td></td>
                                        <td>{{ $branch->branch_name }}</td>
                                        <td>{{ $branch->school_count }}</td>
                                        <td>{{ $branch->teacher_count }}</td>
                                        <td>{{ $branch->student_count }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('refresh-captcha').addEventListener('click', function () {
            fetch('/refresh-captcha')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('captcha-img').src = data.captcha;
                });
        });

        // Initialize Chart
        const ctx = document.getElementById('statisticsChart').getContext('2d');
        const statisticsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Cabang Dinas', 'Sekolah', 'Guru', 'Siswa'],
                datasets: [{
                    label: 'Jumlah',
                    data: [{{ $cabdinCount }}, {{ $schoolCount }}, {{ $teacherCount }}, {{ $studentCount }}],
                    backgroundColor: [
                        '#2ecc71',
                        '#3498db',
                        '#f1c40f',
                        '#e67e22'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const value = context.raw || 0;
                                return `${context.label}: ${value.toLocaleString()}`;
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // Initialize DataTable with hierarchical rows
        const table = $('#statisticsTable').DataTable({
            paging: false,
            searching: false,
            info: false,
            columnDefs: [
                {
                    targets: 0,
                    className: 'details-control',
                    orderable: false,
                    data: null,
                    defaultContent: '+',
                    width: '10%'
                }
            ]
        });

        // Add event listener for opening and closing details
        $('#statisticsTable tbody').on('click', 'td.details-control', function () {
            const tr = $(this).closest('tr');
            const row = table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                $(this).html('+');
            } else {
                // Open this row
                const branchId = tr.data('branch-id');
                const branches = @json($branches);
                const branch = branches.find(b => b.id == branchId);

                row.child(format(branch)).show();
                tr.addClass('shown');
                $(this).html('-');
            }
        });

        function format(branch) {
            let html = '<table class="table table-sm school-table"><thead><tr><th></th><th>Nama Sekolah</th><th>Jumlah Guru</th><th>Jumlah Siswa</th></tr></thead><tbody>';
            branch.schools.forEach(school => {
                html += `<tr data-school-id="${school.id}">
                    <td class="school-control" style="cursor:pointer; width:20px;">+</td>
                    <td>${school.school_name}</td>
                    <td>${school.employees.length}</td>
                    <td>${school.students.length}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            return html;
        }

        // Add event listener for school expansion
        $('#statisticsTable').on('click', '.school-control', function() {
            const tr = $(this).closest('tr');
            const schoolId = tr.data('school-id');
            const branches = @json($branches);
            const school = branches.flatMap(b => b.schools).find(s => s.id == schoolId);

            if (school) {
                if (tr.next().hasClass('school-details-row')) {
                    // Already expanded, collapse
                    tr.next().remove();
                    $(this).html('+');
                } else {
                    // Expand
                    tr.after(`<tr class="school-details-row"><td colspan="4">${formatSchoolDetails(school)}</td></tr>`);
                    $(this).html('-');
                }
            }
        });

        function formatSchoolDetails(school) {
            let html = '<div class="row"><div class="col-md-6"><h6>Guru</h6><table class="table table-sm"><thead><tr><th>Nama Guru</th></tr></thead><tbody>';
            school.employees.forEach(employee => {
                html += `<tr><td>${employee.employee_name}</td></tr>`;
            });
            html += '</tbody></table></div><div class="col-md-6"><h6>Siswa</h6><table class="table table-sm"><thead><tr><th>Nama Siswa</th></tr></thead><tbody>';
            school.students.forEach(student => {
                html += `<tr><td>${student.student_name}</td></tr>`;
            });
            html += '</tbody></table></div></div>';
            return html;
        }
    });
</script>

@endpush
