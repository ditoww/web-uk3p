@extends('layout.body')
@section('konten')

@if (session('success'))
  <div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Kelola Berita</h4>
    <a href="{{ route('kelola.berita.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Berita</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr><th>Judul</th><th>Status</th><th>Penulis</th><th>Dibuat</th><th class="text-end">Aksi</th></tr>
      </thead>
      <tbody>
        @forelse ($posts as $post)
          <tr>
            <td>
              <div class="fw-semibold">{{ $post->title }}</div>
              <small class="text-muted">/berita/{{ $post->slug }}</small>
            </td>
            <td>
              @if ($post->is_published)
                <span class="badge bg-label-success">Terbit</span>
              @else
                <span class="badge bg-label-secondary">Draf</span>
              @endif
            </td>
            <td>{{ $post->author_name }}</td>
            <td>{{ $post->created_at->format('d-m-Y H:i') }}</td>
            <td class="text-end">
              @if ($post->is_published)
                <a href="{{ route('berita.detail', $post->slug) }}" target="_blank" class="btn btn-sm btn-label-secondary">Lihat</a>
              @endif
              <a href="{{ route('kelola.berita.edit', $post) }}" class="btn btn-sm btn-label-primary">Edit</a>
              <form action="{{ route('kelola.berita.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus berita ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-label-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-4">Belum ada berita. Klik "Tambah Berita" untuk membuat yang pertama.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection