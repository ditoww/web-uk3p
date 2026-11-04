@extends('layout.app')

@section('title', 'Note Developer')

@section('styles')
<style>
  .notes-wrapper { height: 100vh; display: flex; gap: 16px; padding: 16px; }
  .notes-sidebar {
    width: 320px; min-width: 260px; background: #fff; border: 1px solid #dee2e6;
    border-radius: 12px; padding: 12px; display: flex; flex-direction: column;
  }
  .sidebar-header { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
  .sidebar-actions { display: flex; gap: 8px; margin-bottom: 8px; }
  .notes-list { overflow-y: auto; flex: 1; border-top: 1px solid #f1f3f5; margin-top: 8px; padding-top: 8px; }
  .note-item { display: block; padding: 10px 12px; border-radius: 8px; text-decoration: none; color: #212529; }
  .note-item:hover { background: #f8f9fa; }
  .note-item.active { background: #eaf1ff; color: #0d6efd; }
  .note-title { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .note-meta { font-size: 12px; color: #6c757d; }
  .notes-card { background: #fff; border: 1px solid #dee2e6; border-radius: 12px; padding: 16px; display: flex; flex-direction: column; flex: 1; min-width: 0; }
  .notes-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
  .notes-actions { display: flex; gap: 8px; align-items: center; }
  .note-title-input { border: none; font-weight: 700; font-size: 1.15rem; outline: none; width: 100%; padding: 4px 8px; border-radius: 8px; }
  .note-title-input:focus { background: #f8f9fa; }
  .notes-body { display: flex; gap: 16px; flex: 1; min-height: 0; }
  .notes-editor, .notes-preview { flex: 1; min-width: 0; display: flex; flex-direction: column; }
  .notes-textarea { width: 100%; height: 100%; resize: none; min-height: 280px; }
  .preview-box { border: 1px solid #dee2e6; border-radius: 8px; padding: 12px; background: #f8f9fa; overflow: auto; white-space: pre-wrap; word-wrap: break-word; flex: 1; }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
  .dot-green { background: #20c997; } .dot-gray { background: #adb5bd; } .dot-red { background: #dc3545; }
  .one-liner { overflow: hidden; white-space: nowrap; }
</style>
@endsection

@section('content')
<div class="notes-wrapper">
  <aside class="notes-sidebar">
    <div class="sidebar-header">
      <div class="fw-bold">My Notes</div>
      <span class="ms-auto badge bg-secondary">{{ $notes->count() }}</span>
    </div>
    <div class="sidebar-actions">
      <button id="newNoteBtn" class="btn btn-sm btn-primary w-100">
        <i class="bi bi-plus-lg"></i> Tambah Note
      </button>
    </div>
    <div class="notes-list">
      @forelse ($notes as $n)
        <a class="note-item {{ optional($activeNote)->id === $n->id ? 'active' : '' }}"
           href="{{ route('DevNotes.index', ['id' => $n->id]) }}">
          <div class="note-title">{{ $n->title ?? 'Untitled' }}</div>
          <div class="note-meta">
            {{ $n->updated_at ? $n->updated_at->diffForHumans() : '—' }} • {{ number_format((int)($n->size ?? 0)) }} B
          </div>
        </a>
      @empty
        <div class="text-muted small">Belum ada catatan. Buat yang pertama.</div>
      @endforelse
    </div>
  </aside>

  <div class="notes-card">
    <div class="notes-header">
      <input id="titleInput" class="note-title-input" placeholder="Untitled"
             value="{{ optional($activeNote)->title ?? '' }}" {{ $activeNote ? '' : 'disabled' }}>
      <div class="notes-actions">
        <small id="saveStatus" class="text-muted one-liner">
          <span id="statusDot" class="status-dot dot-gray"></span>
          <span id="statusText">Idle</span>
        </small>
        <button type="button" id="saveBtn" class="btn btn-sm btn-primary one-liner" {{ $activeNote ? '' : 'disabled' }}>
          <i class="bi bi-save"></i> Simpan
        </button>
        @if($activeNote)
          <a href="{{ route('DevNotes.download', $activeNote->id) }}" class="btn btn-sm btn-outline-secondary one-liner">
            <i class="bi bi-download"></i> Unduh .txt
          </a>
          <button type="button" id="deleteBtn" class="btn btn-sm btn-outline-danger one-liner">
            <i class="bi bi-trash"></i> Hapus
          </button>
        @endif
      </div>
    </div>

    <div class="notes-body">
      <div class="notes-editor">
        <label for="notesTextarea" class="form-label">Editor</label>
        <textarea id="notesTextarea" class="form-control notes-textarea"
                  placeholder="{{ $activeNote ? 'Tulis catatan Anda di sini...' : 'Pilih atau buat note baru' }}"
                  {{ $activeNote ? '' : 'disabled' }}>{{ $noteContent ?? '' }}</textarea>
      </div>
      <div class="notes-preview">
        <label class="form-label">Preview</label>
        <div id="previewBox" class="preview-box">{{ $noteContent ?? '' }}</div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  const activeNoteId = {{ optional($activeNote)->id ?? 'null' }};
  const routes = {
    create: "{{ route('DevNotes.create') }}",
    save: activeNoteId ? "{{ route('DevNotes.save', ['note' => optional($activeNote)->id ?? 0]) }}" : null,
    rename: activeNoteId ? "{{ route('DevNotes.rename', ['note' => optional($activeNote)->id ?? 0]) }}" : null,
    destroy: activeNoteId ? "{{ route('DevNotes.destroy', ['note' => optional($activeNote)->id ?? 0]) }}" : null,
  };

  const textarea = document.getElementById('notesTextarea');
  const preview = document.getElementById('previewBox');
  const saveBtn = document.getElementById('saveBtn');
  const titleInput = document.getElementById('titleInput');
  const deleteBtn = document.getElementById('deleteBtn');
  const newNoteBtn = document.getElementById('newNoteBtn');
  const statusText = document.getElementById('statusText');
  const statusDot = document.getElementById('statusDot');

  let saveTimer = null;
  let inFlight = false;

  function setStatus(text, color = 'gray') {
    statusText.textContent = text;
    statusDot.className = 'status-dot ' + (color === 'green' ? 'dot-green' : color === 'red' ? 'dot-red' : 'dot-gray');
  }

  function debounceSave() {
    setStatus('Mengetik...', 'gray');
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(saveNow, 700);
  }

  async function saveNow() {
    if (!routes.save || inFlight) return;
    inFlight = true;
    setStatus('Menyimpan...', 'gray');

    try {
      const res = await fetch(routes.save, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ content: textarea.value })
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      setStatus('Disimpan', 'green');
    } catch (e) {
      console.error(e);
      setStatus('Gagal menyimpan', 'red');
    } finally { inFlight = false; }
  }

  textarea?.addEventListener('input', () => { preview.textContent = textarea.value; debounceSave(); });
  saveBtn?.addEventListener('click', saveNow);

  titleInput?.addEventListener('change', async () => {
    if (!routes.rename) return;
    const newTitle = (titleInput.value || '').trim() || 'Untitled';
    try {
      const res = await fetch(routes.rename, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ title: newTitle })
      });
      if (!res.ok) throw new Error();

      // Sinkronkan judul di "My Notes" (sidebar) tanpa reload
      const activeTitleEl = document.querySelector('.notes-list .note-item.active .note-title');
      if (activeTitleEl) {
        activeTitleEl.textContent = newTitle;
        activeTitleEl.setAttribute('title', newTitle);
      }

      // Opsional: update meta waktu menjadi "baru saja"
      const metaEl = document.querySelector('.notes-list .note-item.active .note-meta');
      if (metaEl) {
        const sizePart = metaEl.textContent.split('•').slice(1).join('•').trim(); // ambil bagian ukuran jika ada
        metaEl.textContent = 'Baru saja' + (sizePart ? ' • ' + sizePart : '');
      }

      setStatus('Judul di-update', 'green');
      setTimeout(() => setStatus('Idle', 'gray'), 1000);
    } catch {
      setStatus('Gagal rename', 'red');
    }
  });

  deleteBtn?.addEventListener('click', async () => {
    if (!routes.destroy) return;
    if (!confirm('Apakah Anda yakin untuk menghapus note ini?\nAksi tersebut tidak dapat dibalikkan!')) return;
    try {
      const res = await fetch(routes.destroy, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      if (!res.ok) throw new Error();
      window.location = "{{ route('DevNotes.index') }}";
    } catch { setStatus('Gagal hapus', 'red'); }
  });

  newNoteBtn?.addEventListener('click', async () => {
    try {
      const res = await fetch(routes.create, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ title: 'Untitled' })
      });
      if (!res.ok) throw new Error();
      const data = await res.json();
      window.location = data.redirect ?? "{{ route('DevNotes.index') }}";
    } catch { setStatus('Gagal buat note', 'red'); }
  });
</script>
@endsection