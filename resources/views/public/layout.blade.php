<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'UKKKP UNESA')</title>
  <link rel="icon" href="{{ asset('img/logo-ukkkp.jpg') }}">
  <meta name="description" content="Unit Kegiatan Kerohanian Kristen Protestan Universitas Negeri Surabaya">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root { --brand:#1f3a68; --accent:#c9a227; }
    .navbar { background: var(--brand); }
    .navbar .nav-link.active { color:#fff; border-bottom:2px solid var(--accent); }
    .hero { background: linear-gradient(135deg, var(--brand), #2f5597); color:#fff; }
    .btn-accent { background: var(--accent); color:#1b1b1b; border:0; }
    .btn-accent:hover { background:#b08f1f; color:#1b1b1b; }
    .pillar-icon { font-size:2rem; color: var(--accent); }
    footer { background:#14284a; color:#cfd8e8; }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
        <img src="{{ asset('img/logo-ukkkp.jpg') }}" alt="Logo UKKKP UNESA" width="36" height="36" class="rounded-circle bg-white p-1" style="object-fit:contain">
        UKKKP UNESA
      </a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('profil') ? 'active' : '' }}" href="{{ route('profil') }}">Profil</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('kegiatan') ? 'active' : '' }}" href="{{ route('kegiatan') }}">Kegiatan</a></li>
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('kontak') ? 'active' : '' }}" href="{{ route('kontak') }}">Kontak</a></li>
        </ul>
        @auth
          <a class="btn btn-outline-light btn-sm" href="{{ route('dashboard') }}">Dashboard</a>
        @else
          <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Masuk</a>
        @endauth
      </div>
    </div>
  </nav>

  <main class="flex-grow-1">@yield('content')</main>

  <footer class="py-4 mt-5">
    <div class="container text-center small">
      <div class="fw-semibold mb-1">UKKKP UNESA</div>
      <div>Unit Kegiatan Kerohanian Kristen Protestan &middot; Universitas Negeri Surabaya</div>
      <div class="mt-2">&copy; {{ date('Y') }} UKKKP UNESA</div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>