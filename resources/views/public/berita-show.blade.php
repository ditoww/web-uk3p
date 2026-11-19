@extends('layout.body')
@section('konten')

<div class="card">
  @if ($post->cover_image)
    <img src="{{ asset($post->cover_image) }}" class="card-img-top" alt="{{ $post->title }}" style="max-height:360px;object-fit:cover">
  @endif
  <div class="card-body">
    <h3 class="mb-2">{{ $post->title }}</h3>
    <p class="text-muted small mb-4">{{ optional($post->published_at)->format('d-m-Y') }} &middot; {{ $post->author_name }}</p>
    <div style="line-height:1.8">{!! nl2br(e($post->body)) !!}</div>
    <hr class="my-4">
    <a href="{{ route('berita') }}" class="btn btn-label-secondary">&larr; Kembali ke daftar berita</a>
  </div>
</div>

@endsection