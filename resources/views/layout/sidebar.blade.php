<ul class="menu-inner py-1">

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Menu Utama</span>
</li>

<li class="menu-item {{ Request::routeIs('home') ? 'active' : '' }}">
    <a href="{{ route('home') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div data-i18n="Beranda">Beranda</div>
    </a>
</li>
<li class="menu-item {{ Request::routeIs('profil') ? 'active' : '' }}">
    <a href="{{ route('profil') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-info-circle"></i>
        <div data-i18n="Profil">Profil</div>
    </a>
</li>
<li class="menu-item {{ Request::routeIs('kegiatan') ? 'active' : '' }}">
    <a href="{{ route('kegiatan') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-calendar-event"></i>
        <div data-i18n="Kegiatan">Kegiatan</div>
    </a>
</li>
<li class="menu-item {{ Request::routeIs('kontak') ? 'active' : '' }}">
    <a href="{{ route('kontak') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-phone"></i>
        <div data-i18n="Kontak">Kontak</div>
    </a>
</li>

@auth
@if(in_array(Auth::user()->privLevel, ['admin', 'super_admin']))

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Akun</span>
</li>

@if(Auth::user()->isSuperAdmin())
<li class="menu-item {{ Request::is('admin/admin*') ? 'active' : '' }}">
    <a href="/admin/admin" class="menu-link">
        <i class="menu-icon tf-icons ti ti-user"></i>
        <div data-i18n="Akun Admin">Akun Admin</div>
    </a>
</li>
@endif

<li class="menu-item {{ (Request::is('admin/akun*') || Request::is('akun*')) ? 'active' : '' }}">
    <a href="/admin/akun" class="menu-link">
        <i class="menu-icon tf-icons ti ti-user-search"></i>
        <div data-i18n="Akun Developer">Akun Developer</div>
    </a>
</li>
<li class="menu-item {{ Request::is('admin/supervisor*') ? 'active' : '' }}">
    <a href="/admin/supervisor" class="menu-link">
        <i class="menu-icon tf-icons ti ti-user-plus"></i>
        <div data-i18n="Akun Supervisor">Akun Supervisor</div>
    </a>
</li>
<li class="menu-item {{ Request::is('admin/support*') ? 'active' : '' }}">
    <a href="/admin/support" class="menu-link">
        <i class="menu-icon tf-icons ti ti-users"></i>
        <div data-i18n="Akun Support">Akun Support</div>
    </a>
</li>

@if(Auth::user()->isSuperAdmin())
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Tiket</span>
</li>
<li class="menu-item {{ Request::is('admin/ticketsupport*') ? 'active' : '' }}">
    <a href="/admin/ticketsupport" class="menu-link">
        <i class="menu-icon tf-icons ti ti-ticket"></i>
        <div data-i18n="Tiket Support">Tiket Support</div>
    </a>
</li>
<li class="menu-item {{ Request::is('admin/ticketclient*') ? 'active' : '' }}">
    <a href="/admin/ticketclient" class="menu-link">
        <i class="menu-icon tf-icons ti ti-ticket"></i>
        <div data-i18n="Tiket Client">Tiket Client</div>
    </a>
</li>

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">DLL</span>
</li>
<li class="menu-item {{ Request::is('admin/role*') ? 'active' : '' }}">
    <a href="/admin/role" class="menu-link">
        <i class="menu-icon tf-icons ti ti-id-badge"></i>
        <div data-i18n="Role">Role</div>
    </a>
</li>
<li class="menu-item {{ Request::is('admin/application*') ? 'active' : '' }}">
    <a href="/admin/application" class="menu-link">
        <i class="menu-icon tf-icons ti ti-badge"></i>
        <div data-i18n="Aplikasi">Aplikasi</div>
    </a>
</li>
<li class="menu-item {{ Request::is('admin/password*') ? 'active' : '' }}">
    <a href="/admin/password" class="menu-link">
        <i class="menu-icon tf-icons ti ti-eye-off"></i>
        <div data-i18n="Reset Password">Reset Password</div>
    </a>
</li>
@endif

@else

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Pekerjaan</span>
</li>
<li class="menu-item">
    <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-ticket"></i>
        <div data-i18n="Dashboard Tiket">Dashboard Tiket</div>
    </a>
</li>

@endif
@endauth

</ul>