@extends('layout.body')
@section('konten')

<div class="card">
  <div class="card-header">
    <h4 class="mb-0">{{ $post->exists ? 'Edit Berita' : 'Tambah Berita' }}</h4>
  </div>
  <div class="card-body">
    <form action="{{ $post->exists ? route('kelola.berita.update', $post) : route('kelola.berita.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($post->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label" for="title">Judul</label>
        <input type="text" id="title" name="title" maxlength="150" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="mb-3">
        <label class="form-label" for="body">Isi Berita</label>
        <textarea id="body" name="body" rows="12" class="form-control @error('body') is-invalid @enderror" required>{{ old('body', $post->body) }}</textarea>
        @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="text-muted">Teks biasa. Baris baru akan dipertahankan di halaman berita.</small>
      </div>

      <div class="mb-3">
        <label class="form-label" for="cover">Foto Sampul (opsional, JPG/PNG/WEBP, maks. 2 MB)</label>
        @if ($post->cover_image)
          <div class="mb-2"><img src="{{ asset($post->cover_image) }}" alt="Sampul saat ini" style="max-height:140px" class="rounded border"></div>
        @endif
        <input type="file" id="cover" name="cover" accept=".jpg,.jpeg,.png,.webp" class="form-control @error('cover') is-invalid @enderror">
        @error('cover') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_published">Terbitkan (tampil di website)</label>
      </div>

      <button type="submit" class="btn btn-primary me-2">Simpan</button>
      <a href="{{ route('kelola.berita.index') }}" class="btn btn-label-secondary">Batal</a>
    </form>
  </div>
</div>

@endsection