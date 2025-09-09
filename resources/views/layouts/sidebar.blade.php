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

            <li><a class="nav-link" href=""><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a class="nav-link" href="{{ route('cabdin.index') }}"><i class="fas fa-building"></i> <span>Data Cabdin</span></a></li>
            <li><a class="nav-link" href="{{ route('sekolah.index') }}"><i class="fas fa-school"></i> <span>Data Sekolah</span></a></li>
            <li><a class="nav-link" href="{{ route('pegawai.index') }}"><i class="fas fa-users"></i> <span>Data Guru & Staff</span></a></li>
            <li><a class="nav-link" href="{{ route('siswa.index') }}"><i class="fas fa-user-friends"></i> <span>Data Siswa</span></a></li>
        </ul>
        <!-- End Menu -->
    </aside>
</div>
