@extends('layout.body')
@section('konten')

<h4 class="mb-4">Kontak dan Bergabung</h4>

<div class="row g-4">
  <div class="col-md-6"><div class="card h-100"><div class="card-body">
    <h5>Sekretariat</h5>
    <p class="text-muted">{!! $alamat !== '' ? \App\Support\Teks::tautan($alamat) : 'Alamat akan segera ditambahkan.' !!}</p>

    <h5 class="mt-4">Media Sosial dan Kontak</h5>
    @forelse ($kontak as $k)
      <div class="d-flex align-items-center mb-2">
        <i class="ti {{ $k->icon ?: 'ti-world' }} ti-md me-2 text-primary"></i>
        <div>
          <span class="fw-semibold">{{ $k->title }}</span>:
          @if ($k->link)
            <a href="{{ $k->link }}" target="_blank" rel="noopener noreferrer">{{ $k->subtitle ?: $k->link }}</a>
          @else
            {{ $k->subtitle }}
          @endif
        </div>
      </div>
    @empty
      <p class="text-muted mb-0">Kontak akan segera ditambahkan.</p>
    @endforelse
  </div></div></div>

  <div class="col-md-6"><div class="card h-100"><div class="card-body">
    <h5>Cara Bergabung</h5>
    <p class="text-muted mb-0">{!! $bergabung !== '' ? \App\Support\Teks::tautan($bergabung) : 'Informasi pendaftaran akan segera ditambahkan.' !!}</p>
  </div></div></div>
</div>

@endsection