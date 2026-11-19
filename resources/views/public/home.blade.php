@extends('layout.body')
@section('konten')

<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
      <img src="{{ asset('img/logo-ukkkp.jpg') }}" alt="Logo UKKKP UNESA" width="110" height="110" class="rounded-circle border p-1 bg-white" style="object-fit:contain">
      <div class="text-center text-md-start">
        @auth
          <h4 class="mb-1">Halo, {{ Auth::user()->name ?? Auth::user()->username }}</h4>
        @else
          <h4 class="mb-1">Selamat Datang di UKKKP UNESA</h4>
        @endauth
        <p class="mb-3">Unit Kegiatan Kerohanian Kristen Protestan Universitas Negeri Surabaya: wadah pembinaan iman, pelayanan, dan kebersamaan bagi mahasiswa Kristen Protestan.</p>
        <a href="{{ route('kegiatan') }}" class="btn btn-primary me-2">Lihat Kegiatan</a>
        @guest
          <a href="{{ route('login') }}" class="btn btn-label-secondary">Masuk</a>
        @endguest
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  @foreach ([
    ['Pembinaan Iman', 'Pertumbuhan rohani lewat ibadah, pendalaman Alkitab, dan persekutuan.', 'ti-book'],
    ['Pelayanan', 'Melayani sesama di kampus dan masyarakat.', 'ti-heart'],
    ['Kebersamaan', 'Keluarga rohani lintas fakultas dan angkatan.', 'ti-users'],
  ] as [$judul, $isi, $ikon])
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="avatar mb-3"><span class="avatar-initial rounded bg-label-primary"><i class="ti {{ $ikon }} ti-md"></i></span></div>
          <h5>{{ $judul }}</h5>
          <p class="mb-0 text-muted">{{ $isi }} [ISI: detail program]</p>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="row g-4">
  <div class="col-md-6">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Jadwal Ibadah dan Persekutuan</h5>
      <p class="text-muted mb-0">[ISI: hari, jam, dan lokasi ibadah rutin]</p>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Pengumuman</h5>
      <p class="text-muted mb-0">Belum ada pengumuman. Bagian ini akan bisa diisi lewat panel admin.</p>
    </div></div>
  </div>
</div>

@endsection