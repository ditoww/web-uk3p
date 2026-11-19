@extends('layout.body')
@section('konten')

<h4 class="mb-4">Berita dan Pengumuman</h4>

<div class="row g-4">
  @forelse ($posts as $post)
    <div class="col-md-4">
      <div class="card h-100">
        @if ($post->cover_image)
          <img src="{{ asset($post->cover_image) }}" class="card-img-top" alt="{{ $post->title }}" style="height:180px;object-fit:cover">
        @endif
        <div class="card-body">
          <h5 class="card-title"><a href="{{ route('berita.detail', $post->slug) }}">{{ $post->title }}</a></h5>
          <p class="text-muted small mb-2">{{ optional($post->published_at)->format('d-m-Y') }} &middot; {{ $post->author_name }}</p>
          <p class="mb-0">{{ $post->excerpt }}</p>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted">Belum ada berita yang diterbitkan.</div></div></div>
  @endforelse
</div>

@endsection