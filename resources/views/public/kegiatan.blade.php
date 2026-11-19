@extends('layout.body')
@section('konten')

<h4 class="mb-4">Kegiatan</h4>

<div class="row g-4">
  @foreach ([
    ['Ibadah Rutin', '[ISI: deskripsi dan jadwal]', 'ti-calendar-event'],
    ['Persekutuan Kelompok Kecil', '[ISI: deskripsi dan jadwal]', 'ti-users'],
    ['Pelayanan dan Bakti Sosial', '[ISI: deskripsi]', 'ti-heart'],
    ['Perayaan Hari Besar Kristiani', '[ISI: deskripsi]', 'ti-gift'],
    ['Pelatihan dan Retret', '[ISI: deskripsi]', 'ti-school'],
    ['Penyambutan Mahasiswa Baru', '[ISI: deskripsi]', 'ti-flag'],
  ] as [$judul, $isi, $ikon])
    <div class="col-md-4">
      <div class="card h-100"><div class="card-body">
        <div class="avatar mb-3"><span class="avatar-initial rounded bg-label-primary"><i class="ti {{ $ikon }} ti-md"></i></span></div>
        <h5>{{ $judul }}</h5>
        <p class="text-muted mb-0">{{ $isi }}</p>
      </div></div>
    </div>
  @endforeach
</div>
<p class="text-muted small mt-4">Daftar di atas adalah kerangka contoh. Sesuaikan dengan kegiatan yang benar-benar ada.</p>

@endsection