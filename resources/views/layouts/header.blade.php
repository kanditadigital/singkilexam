<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <div class="d-sm-none d-lg-inline-block">
                    @if(Auth::guard('web')->check())
                        {{ Auth::guard('web')->user()->name }}
                    @elseif(Auth::guard('branches')->check())
                        {{ Auth::guard('branches')->user()->branch_name }}
                    @elseif(Auth::guard('schools')->check())
                        {{ Auth::guard('schools')->user()->school_name }}
                    @endif
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="dropdown-item"><i class="fas fa-fw fa-power-off"></i> Keluar</button>
                </form>
            </div>
        </li>
    </ul>
</nav>
