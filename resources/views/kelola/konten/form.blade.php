@extends('layout.body')
@section('konten')

@php
  $pub = count(old()) ? (bool) old('is_published') : (bool) $item->is_published;
@endphp

<div class="card">
  <div class="card-header">
    <h4 class="mb-0">{{ $item->exists ? 'Edit' : 'Tambah' }} {{ $cfg['singular'] }}</h4>
  </div>
  <div class="card-body">
    <form action="{{ $item->exists ? route('kelola.konten.perbarui', $item) : route('kelola.konten.simpan', $section) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($item->exists)
        @method('PUT')
      @endif

      @foreach ($cfg['fields'] as $name => $f)
        <div class="mb-3">
          <label class="form-label" for="{{ $name }}">{{ $f['label'] }}@if (! empty($f['required'])) <span class="text-danger">*</span>@endif</label>

          @if ($f['type'] === 'textarea')
            <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $f['rows'] ?? 5 }}" class="form-control @error($name) is-invalid @enderror">{{ old($name, $item->$name) }}</textarea>
          @elseif ($f['type'] === 'icon')
            <select id="{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror">
              <option value="">(tanpa ikon)</option>
              @foreach (config('konten.icons') as $val => $lbl)
                <option value="{{ $val }}" {{ old($name, $item->$name) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
              @endforeach
            </select>
          @elseif ($f['type'] === 'image')
            @if ($item->image)
              <div class="mb-2"><img src="{{ asset($item->image) }}" alt="Foto saat ini" style="max-height:120px" class="rounded border"></div>
            @endif
            <input type="file" id="{{ $name }}" name="{{ $name }}" accept=".jpg,.jpeg,.png,.webp" class="form-control @error($name) is-invalid @enderror">
          @elseif ($f['type'] === 'date')
            <input type="date" id="{{ $name }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" value="{{ old($name, optional($item->$name)->format('Y-m-d')) }}">
          @elseif ($f['type'] === 'number')
            <input type="number" min="0" max="9999" id="{{ $name }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" value="{{ old($name, $item->$name ?? 0) }}">
          @else
            <input type="text" id="{{ $name }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" value="{{ old($name, $item->$name) }}" @if (! empty($f['required'])) required @endif>
          @endif

          @if (! empty($f['help']))
            <small class="text-muted">{{ $f['help'] }}</small>
          @endif
          @error($name)
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
      @endforeach

      <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ $pub ? 'checked' : '' }}>
        <label class="form-check-label" for="is_published">Tampilkan di website</label>
      </div>

      <button type="submit" class="btn btn-primary me-2">Simpan</button>
      <a href="{{ route('kelola.konten.halaman', $cfg['page']) }}" class="btn btn-label-secondary">Batal</a>
    </form>
  </div>
</div>

@endsection