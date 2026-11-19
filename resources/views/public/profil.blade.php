@extends('layout.body')
@section('konten')

<h4 class="mb-4">Profil</h4>

<div class="card mb-4"><div class="card-body">
  <h5>Tentang UKKKP UNESA</h5>
  <p class="mb-0">{!! \App\Support\Teks::tautan($tentang) !!}</p>
</div></div>

<div class="row g-4 mb-4">
  <div class="col-md-4"><div class="card h-100"><div class="card-body">
    <h5>Sejarah</h5>
    <p class="text-muted mb-0">{!! $sejarah !== '' ? \App\Support\Teks::tautan($sejarah) : 'Informasi akan segera ditambahkan.' !!}</p>
  </div></div></div>
  <div class="col-md-4"><div class="card h-100"><div class="card-body">
    <h5>Visi</h5>
    <p class="text-muted mb-0">{!! $visi !== '' ? \App\Support\Teks::tautan($visi) : 'Informasi akan segera ditambahkan.' !!}</p>
  </div></div></div>
  <div class="col-md-4"><div class="card h-100"><div class="card-body">
    <h5>Misi</h5>
    <p class="text-muted mb-0">{!! $misi !== '' ? \App\Support\Teks::tautan($misi) : 'Informasi akan segera ditambahkan.' !!}</p>
  </div></div></div>
</div>

<h5 class="mb-3">Struktur Pengurus</h5>
<div class="row g-4">
  @forelse ($pengurus as $m)
    <div class="col-md-3 col-sm-6">
      <div class="card h-100 text-center">
        <div class="card-body">
          @if ($m->image)
            <img src="{{ asset($m->image) }}" alt="{{ $m->title }}" class="rounded-circle mb-3" style="width:96px;height:96px;object-fit:cover">
          @else
            <div class="avatar avatar-xl mx-auto mb-3"><span class="avatar-initial rounded-circle bg-label-primary">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($m->title, 0, 1)) }}</span></div>
          @endif
          <h6 class="mb-1">{{ $m->title }}</h6>
          <div class="text-muted small">{{ $m->subtitle }}</div>
          @if ($m->body)
            <div class="text-muted small mt-1">{{ $m->body }}</div>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="card"><div class="card-body text-muted">Struktur pengurus akan segera ditambahkan.</div></div></div>
  @endforelse
</div>

@endsection