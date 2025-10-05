<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        @php
            $brandRoute = match (true) {
                Auth::guard('web')->check() => route('dashboard'),
                Auth::guard('branches')->check() => route('cabdin.dashboard'),
                Auth::guard('schools')->check() => route('sch.student.index'),
                default => route('home'),
            };
        @endphp
        <div class="sidebar-brand">
            <a href="{{ $brandRoute }}">
                ADMIN PANEL
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <i class="fas fa-fw fa-code"></i>
        </div>

        <!-- Menu -->
        <ul class="sidebar-menu">
            @if(Auth::guard('web')->check())
                <li>
                    <a class="nav-link" href="{{ route('disdik.office.home') }}">
                        <i class="fas fa-home"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li><a class="nav-link" href="{{ route('disdik.cabdin.index') }}"><i class="fas fa-building"></i> <span>Data Cabdin</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.sekolah.index') }}"><i class="fas fa-school"></i> <span>Data Sekolah</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.pegawai.index') }}"><i class="fas fa-users"></i> <span>Data Guru & Staff</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.siswa.index') }}"><i class="fas fa-user-friends"></i> <span>Data Siswa</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.exam.index') }}"><i class="fas fa-file-alt"></i> <span>Data Ujian</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.mapel.index') }}"><i class="fas fa-book"></i> <span>Data Mata Pelajaran</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.sesi-ujian.index') }}"><i class="fas fa-calendar-alt"></i> <span>Data Sesi Ujian</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.live-score.index') }}"><i class="fas fa-broadcast-tower"></i> <span>Live Score</span></a></li>
                <li><a class="nav-link" href="{{ route('disdik.ranking.index') }}"><i class="fas fa-trophy"></i> <span>Perengkingan</span></a></li>
            @elseif(Auth::guard('branches')->check())
                <li><a class="nav-link" href="{{ route('cabdin.dashboard') }}"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                <li><a class="nav-link" href="{{ route('cabdin.schools.index') }}"><i class="fas fa-school"></i> <span>Data Sekolah</span></a></li>
                <li><a class="nav-link" href="{{ route('cabdin.students.index') }}"><i class="fas fa-user-graduate"></i> <span>Data Siswa</span></a></li>
                <li><a class="nav-link" href="{{ route('cabdin.exam-participants.index') }}"><i class="fas fa-user-check"></i> <span>Peserta Ujian</span></a></li>
                <li><a class="nav-link" href="{{ route('cabdin.profile.edit') }}"><i class="fas fa-user"></i> <span>Profil</span></a></li>
            @elseif(Auth::guard('schools')->check())
            <li><a class="nav-link" href="{{ route('sch.employee.index') }}"><i class="fas fa-users"></i> <span>Data Guru & Staff</span></a></li>
            <li><a class="nav-link" href="{{ route('sch.student.index') }}"><i class="fas fa-user-friends"></i> <span>Data Siswa</span></a></li>
            <li><a class="nav-link" href="{{ route('sch.exam-participants.index') }}"><i class="fas fa-user-check"></i> <span>Peserta Ujian</span></a></li>
            <li><a class="nav-link" href="{{ route('sch.participant-cards.index') }}"><i class="fas fa-id-card"></i> <span>Kartu Peserta</span></a></li>
            <li><a class="nav-link" href="{{ route('sch.exam-monitoring.index') }}"><i class="fas fa-desktop"></i> <span>Monitoring Ujian</span></a></li>
            <li><a class="nav-link" href="{{ route('sch.profile.edit') }}"><i class="fas fa-user"></i> <span>Profil</span></a></li>
            @endif
        </ul>
        <!-- End Menu -->
    </aside>
</div>
