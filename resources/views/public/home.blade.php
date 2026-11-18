@extends('public.layout')
@section('title', 'UKKKP UNESA - Unit Kegiatan Kerohanian Kristen Protestan')

@section('content')
<section class="hero py-5">
  <div class="container py-4 text-center">
    <h1 class="display-5 fw-bold">UKKKP UNESA</h1>
    <p class="lead mb-4">Wadah pembinaan iman, pelayanan, dan kebersamaan bagi mahasiswa Kristen Protestan Universitas Negeri Surabaya.</p>
    <a href="{{ route('kegiatan') }}" class="btn btn-accent btn-lg me-2">Lihat Kegiatan</a>
    <a href="{{ route('kontak') }}" class="btn btn-outline-light btn-lg">Bergabung</a>
  </div>
</section>

<section class="container py-5">
  <div class="row g-4 text-center">
    <div class="col-md-4">
      <div class="pillar-icon">&#10013;</div>
      <h4 class="mt-2">Pembinaan Iman</h4>
      <p class="text-muted">Pertumbuhan rohani lewat ibadah, pendalaman Alkitab, dan persekutuan. [ISI: detail program]</p>
    </div>
    <div class="col-md-4">
      <div class="pillar-icon">&#129309;</div>
      <h4 class="mt-2">Pelayanan</h4>
      <p class="text-muted">Melayani sesama di kampus dan masyarakat. [ISI: detail program]</p>
    </div>
    <div class="col-md-4">
      <div class="pillar-icon">&#128101;</div>
      <h4 class="mt-2">Kebersamaan</h4>
      <p class="text-muted">Keluarga rohani lintas fakultas dan angkatan. [ISI: detail program]</p>
    </div>
  </div>
</section>

<section class="bg-light py-5">
  <div class="container text-center">
    <h3>Jadwal Ibadah &amp; Persekutuan</h3>
    <p class="text-muted mb-0">[ISI: hari, jam, dan lokasi ibadah rutin]</p>
  </div>
</section>
@endsection