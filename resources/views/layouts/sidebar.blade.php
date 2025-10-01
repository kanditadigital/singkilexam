<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="">
                ADMIN PANEL
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <i class="fas fa-fw fa-code"></i>
        </div>

        <!-- Menu -->
        <ul class="sidebar-menu">

            <li><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            @if(Auth::guard('web')->user())
                <li><a class="nav-link" href="{{ route('cabdin.index') }}"><i class="fas fa-building"></i> <span>Data Cabdin</span></a></li>
                <li><a class="nav-link" href="{{ route('sekolah.index') }}"><i class="fas fa-school"></i> <span>Data Sekolah</span></a></li>
                <li><a class="nav-link" href="{{ route('pegawai.index') }}"><i class="fas fa-users"></i> <span>Data Guru & Staff</span></a></li>
                <li><a class="nav-link" href="{{ route('siswa.index') }}"><i class="fas fa-user-friends"></i> <span>Data Siswa</span></a></li>
                <li><a class="nav-link" href="{{ route('exam.index') }}"><i class="fas fa-file-alt"></i> <span>Data Ujian</span></a></li>
                <li><a class="nav-link" href="{{ route('mapel.index') }}"><i class="fas fa-book"></i> <span>Data Mata Pelajaran</span></a></li>
                <li><a class="nav-link" href="{{ route('sesi-ujian.index') }}"><i class="fas fa-calendar-alt"></i> <span>Data Sesi Ujian</span></a></li>
                <li><a class="nav-link" href="{{ route('live-score.index') }}"><i class="fas fa-broadcast-tower"></i> <span>Live Score</span></a></li>

            @elseif(Auth::guard('schools')->user())
                <li><a class="nav-link" href="{{ route('employee.index') }}"><i class="fas fa-users"></i> <span>Data Guru & Staff</span></a></li>
                <li><a class="nav-link" href="{{ route('student.index') }}"><i class="fas fa-user-friends"></i> <span>Data Siswa</span></a></li>
                <li><a class="nav-link" href="{{ route('exam-participants.index') }}"><i class="fas fa-user-check"></i> <span>Peserta Ujian</span></a></li>
            @endif
        </ul>
        <!-- End Menu -->
    </aside>
</div>
