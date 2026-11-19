@extends('layout.body')
@section('konten')

<h4 class="mb-4">Kegiatan</h4>

<div class="row g-4">
  @forelse ($kegiatan as $k)
    <div class="col-md-4">
      <div class="card h-100">
        @if ($k->image)
          <img src="{{ asset($k->image) }}" class="card-img-top" alt="{{ $k->title }}" style="height:180px;object-fit:cover">
        @endif
        <div class="card-body">
          <div class="avatar mb-3"><span class="avatar-initial rounded bg-label-primary"><i class="ti {{ $k->icon ?: 'ti-star' }} ti-md"></i></span></div>
          <h5>{{ $k->title }}</h5>
          @if ($k->subtitle)
            <div class="small mb-2"><i class="ti ti-clock me-1"></i>{{ $k->subtitle }}</div>
          @endif
          <p class="text-muted mb-0">{!! $k->body ? \App\Support\Teks::tautan($k->body) : 'Detail kegiatan akan segera ditambahkan.' !!}</p>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="card"><div class="card-body text-muted">Belum ada kegiatan yang ditampilkan.</div></div></div>
  @endforelse
</div>

@endsection