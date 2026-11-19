@extends('layout.body')
@section('konten')

@if (session('success'))
  <div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Kelola Halaman {{ $cfg['label'] }}</h4>
  <a href="{{ route($cfg['route']) }}" target="_blank" class="btn btn-label-secondary">Lihat halaman</a>
</div>

@if (! empty($cfg['settings']))
<div class="card mb-4">
  <div class="card-header"><h5 class="mb-0">Teks Halaman</h5></div>
  <div class="card-body">
    <form action="{{ route('kelola.konten.teks', $page) }}" method="POST">
      @csrf
      @foreach ($cfg['settings'] as $key => $f)
        <div class="mb-3">
          <label class="form-label" for="{{ $key }}">{{ $f['label'] }}</label>
          @if ($f['type'] === 'textarea')
            <textarea id="{{ $key }}" name="{{ $key }}" rows="5" class="form-control">{{ old($key, $values[$key]) }}</textarea>
          @else
            <input type="text" id="{{ $key }}" name="{{ $key }}" class="form-control" value="{{ old($key, $values[$key]) }}">
          @endif
        </div>
      @endforeach
      <button type="submit" class="btn btn-primary">Simpan Teks</button>
    </form>
  </div>
</div>
@endif

@foreach ($sections as $key => $s)
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">{{ $s['cfg']['label'] }}</h5>
    <a href="{{ route('kelola.konten.tambah', $key) }}" class="btn btn-primary btn-sm">Tambah {{ $s['cfg']['singular'] }}</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th style="width:80px">Urutan</th>
          <th>{{ $s['cfg']['fields']['title']['label'] }}</th>
          <th>Keterangan</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($s['items'] as $it)
          <tr>
            <td>{{ $it->sort_order }}</td>
            <td class="fw-semibold">{{ $it->title }}</td>
            <td class="text-muted">{{ $it->event_date ? \App\Support\Teks::tanggal($it->event_date) : ($it->subtitle ?: \Illuminate\Support\Str::limit(strip_tags((string) $it->body), 60)) }}</td>
            <td>
              @if ($it->is_published)
                <span class="badge bg-label-success">Tampil</span>
              @else
                <span class="badge bg-label-secondary">Disembunyikan</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('kelola.konten.ubah', $it) }}" class="btn btn-sm btn-label-primary">Edit</a>
              <form action="{{ route('kelola.konten.hapus', $it) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-label-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data. Klik "Tambah {{ $s['cfg']['singular'] }}".</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endforeach

@endsection