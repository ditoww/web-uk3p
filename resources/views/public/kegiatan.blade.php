@extends('public.layout')
@section('title', 'Kegiatan - UKKKP UNESA')

@section('content')
<div class="container py-5">
  <h1 class="mb-4">Kegiatan</h1>
  <div class="row g-4">
    @foreach ([
      ['Ibadah Rutin', '[ISI: deskripsi dan jadwal]'],
      ['Persekutuan Kelompok Kecil', '[ISI: deskripsi dan jadwal]'],
      ['Pelayanan &amp; Bakti Sosial', '[ISI: deskripsi]'],
      ['Perayaan Hari Besar Kristiani', '[ISI: deskripsi]'],
      ['Pelatihan &amp; Retret', '[ISI: deskripsi]'],
      ['Penyambutan Mahasiswa Baru', '[ISI: deskripsi]'],
    ] as [$judul, $isi])
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">{!! $judul !!}</h5>
            <p class="card-text text-muted">{!! $isi !!}</p>
          </div>
        </div>
      </div>
    @endforeach
  </div>
  <p class="text-muted mt-4 small">Daftar di atas contoh kerangka. Sesuaikan dengan kegiatan yang benar-benar ada.</p>
</div>
@endsection