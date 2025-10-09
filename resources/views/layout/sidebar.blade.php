<ul class="menu-inner py-1">
    {{--
    <!-- Dashboards -->
    <br>
    <li class="menu-item {{ Request::is('dashboard/admin') ? 'active' : '' }}">
        <a href="/dashboard/admin" class="menu-link ">
            <i class="menu-icon tf-icons ti ti-smart-home"></i>
            <div data-i18n="Dashboards">Dashboards</div>
            {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}} {{--
        </a>
    </li>
    <br>--}}
    <!-- Apps & Pages -->
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Akun</span>
</li>

    {{-- akun --}}
@if(Auth::user()->isSuperAdmin())
<div class="mt-1"></div>

<li class="menu-item {{ Request::is('admin/admin*') ? 'active' : '' }}">
    <a href="/admin/admin" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-user"></i>
        <div data-i18n="Akun Admin">Akun Admin</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>
@endif

<div class="mt-1"></div>

<li class="menu-item {{ (Request::is('admin/akun*') || Request::is('akun*')) ? 'active' : '' }}">
    <a href="/admin/akun" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-user-search"></i>
        <div data-i18n="Akun Developer">Akun Developer</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>

<div class="mt-1"></div>
<li class="menu-item {{ Request::is('admin/supervisor*') ? 'active' : '' }}">
    <a href="/admin/supervisor" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-user-plus"></i>
        <div data-i18n="Akun Supervisor">Akun Supervisor</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>   

<div class="mt-1"></div>
<li class="menu-item {{ Request::is('admin/support*') ? 'active' : '' }}">
    <a href="/admin/support" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-users"></i>
        <div data-i18n="Akun Support">Akun Support</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>
@if(Auth::user()->isSuperAdmin())
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Tiket</span>
</li>
<div class="mt-1"></div>
<li class="menu-item {{ Request::is('admin/ticketsupport*') ? 'active' : '' }}">
    <a href="/admin/ticketsupport" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-ticket"></i>
        <div data-i18n="Tiket Support">Tiket Support</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>

<div class="mt-1"></div>
<li class="menu-item {{ Request::is('admin/ticketclient*') ? 'active' : '' }}">
    <a href="/admin/ticketclient" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-ticket"></i>
        <div data-i18n="Tiket Client">Tiket Client</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">DLL</span>
</li>

<div class="mt-1"></div>
<li class="menu-item {{ Request::is('admin/role*') ? 'active' : '' }}">
    <a href="/admin/role" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-id-badge"></i>
        <div data-i18n="Role">Role</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>

<div class="mt-1"></div>
<li class="menu-item {{ Request::is('admin/application*') ? 'active' : '' }}">
    <a href="/admin/application" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-badge"></i>
        <div data-i18n="Aplikasi">Aplikasi</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>
<div class="mt-1"></div>
<li class="menu-item {{ Request::is('admin/password*') ? 'active' : '' }}">
    <a href="/admin/password" class="menu-link ">
        <i class="menu-icon tf-icons ti ti-eye-off"></i>
        <div data-i18n="Reset Password">Reset Password</div>
        {{-- <div class="badge bg-label-primary rounded-pill ms-auto">3</div> --}}
    </a>
</li>
@endif

  

</ul>




















