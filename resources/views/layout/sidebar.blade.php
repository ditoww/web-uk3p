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
    <span class="menu-header-text">Kelola Konten</span>
</li>
<li class="menu-item {{ Request::is('kelola/beranda*') ? 'active' : '' }}">
    <a href="{{ route('kelola.konten.halaman', 'beranda') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-edit"></i>
        <div data-i18n="Kelola Beranda">Kelola Beranda</div>
    </a>
</li>
<li class="menu-item {{ Request::is('kelola/profil*') ? 'active' : '' }}">
    <a href="{{ route('kelola.konten.halaman', 'profil') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-edit"></i>
        <div data-i18n="Kelola Profil">Kelola Profil</div>
    </a>
</li>
<li class="menu-item {{ Request::is('kelola/kegiatan*') ? 'active' : '' }}">
    <a href="{{ route('kelola.konten.halaman', 'kegiatan') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-edit"></i>
        <div data-i18n="Kelola Kegiatan">Kelola Kegiatan</div>
    </a>
</li>
<li class="menu-item {{ Request::is('kelola/kontak*') ? 'active' : '' }}">
    <a href="{{ route('kelola.konten.halaman', 'kontak') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-edit"></i>
        <div data-i18n="Kelola Kontak">Kelola Kontak</div>
    </a>
</li>
@endif

@if(Auth::user()->isSuperAdmin())
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Pengaturan</span>
</li>
<li class="menu-item {{ Request::is('admin/admin*') ? 'active' : '' }}">
    <a href="/admin/admin" class="menu-link">
        <i class="menu-icon tf-icons ti ti-user"></i>
        <div data-i18n="Akun Admin">Akun Admin</div>
    </a>
</li>
<li class="menu-item {{ Request::is('admin/password*') ? 'active' : '' }}">
    <a href="/admin/password" class="menu-link">
        <i class="menu-icon tf-icons ti ti-eye-off"></i>
        <div data-i18n="Reset Password">Reset Password</div>
    </a>
</li>
@endif
@endauth

</ul>