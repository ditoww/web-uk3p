@extends('layout.body')
@section('konten')

<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
      <img src="{{ asset('img/logo-ukkkp.jpg') }}" alt="Logo UKKKP UNESA" width="110" height="110" class="rounded-circle border p-1 bg-white" style="object-fit:contain">
      <div class="text-center text-md-start">
        @auth
          <div class="text-muted small mb-1">Halo, {{ Auth::user()->name ?? Auth::user()->username }}</div>
        @endauth
        <h4 class="mb-2">{{ $judul }}</h4>
        <p class="mb-3">{!! \App\Support\Teks::tautan($intro) !!}</p>
        <a href="{{ route('kegiatan') }}" class="btn btn-primary me-2">Lihat Kegiatan</a>
        @guest
          <a href="{{ route('login') }}" class="btn btn-label-secondary">Masuk</a>
        @endguest
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  @foreach ($pilar as $p)
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="avatar mb-3"><span class="avatar-initial rounded bg-label-primary"><i class="ti {{ $p->icon ?: 'ti-star' }} ti-md"></i></span></div>
          <h5>{{ $p->title }}</h5>
          <p class="mb-0 text-muted">{!! \App\Support\Teks::tautan((string) $p->body) !!}</p>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="card">
  <div class="card-body">
    <h5 class="mb-4"><i class="ti ti-calendar-event me-2 text-primary"></i>Jadwal Ibadah dan Persekutuan</h5>

    @forelse ($jadwal as $j)
      @php $lewat = $j->event_date && $j->event_date->lt(now()->startOfDay()); @endphp
      <div class="border rounded overflow-hidden mb-4 {{ $lewat ? 'opacity-75' : '' }}">

        @if ($j->image)
          <a href="{{ asset($j->image) }}" target="_blank" rel="noopener" class="d-block text-center" style="background:#f5f5f9">
            <img src="{{ asset($j->image) }}" alt="{{ $j->title }}" class="img-fluid" style="max-height:520px;object-fit:contain">
          </a>
        @endif

        <div class="p-4">
          <div class="d-flex flex-column flex-sm-row gap-4">

            @if ($j->event_date)
              <div class="bg-primary text-white rounded text-center flex-shrink-0 align-self-start px-3 py-2" style="min-width:92px;line-height:1.15">
                <div class="small text-uppercase" style="letter-spacing:.08em;opacity:.9">{{ \App\Support\Teks::hari($j->event_date) }}</div>
                <div style="font-size:2.4rem;font-weight:700">{{ $j->event_date->format('j') }}</div>
                <div class="small text-uppercase" style="letter-spacing:.08em;opacity:.9">{{ \App\Support\Teks::bulan($j->event_date, true) }} {{ $j->event_date->format('Y') }}</div>
              </div>
            @endif

            <div class="flex-grow-1" style="min-width:0">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h5 class="mb-0">{{ $j->title }}</h5>
                @if ($lewat)
                  <span class="badge bg-label-secondary">Telah berlangsung</span>
                @endif
              </div>

              <div class="d-flex flex-wrap gap-2 mb-3">
                @if ($j->event_time)
                  <span class="badge bg-label-primary d-inline-flex align-items-center px-3 py-2" style="font-size:.9rem;font-weight:500;text-transform:none"><i class="ti ti-clock me-2"></i>{{ $j->event_time }}</span>
                @elseif (! $j->event_date && $j->subtitle)
                  <span class="badge bg-label-primary d-inline-flex align-items-center px-3 py-2" style="font-size:.9rem;font-weight:500;text-transform:none"><i class="ti ti-clock me-2"></i>{{ $j->subtitle }}</span>
                @endif
                @if ($j->location)
                  <span class="badge bg-label-info d-inline-flex align-items-center px-3 py-2" style="font-size:.9rem;font-weight:500;text-transform:none"><i class="ti ti-map-pin me-2"></i>{{ $j->location }}</span>
                @endif
              </div>

              @if ($j->link)
                <a href="{{ $j->link }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary mb-3"><i class="ti ti-map-pin me-1"></i>Buka di Google Maps</a>
              @endif

              @if ($j->body)
                <div class="text-muted" style="line-height:1.8;overflow-wrap:anywhere">{!! \App\Support\Teks::tautan($j->body) !!}</div>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <p class="text-muted mb-0">Jadwal akan segera diumumkan.</p>
    @endforelse
  </div>
</div>

@endsection