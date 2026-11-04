@extends('layout.app')

@section('title', 'Chat History')

@section('styles')
<style>
  /* Reuse key styles from chatdev view (copy minimal necessary) */
  .chat-body { padding:20px; overflow-y:auto; flex-grow:1; background:#ffffff; border-top:1px solid #dee2e6; border-bottom:1px solid #dee2e6; display:flex; flex-direction:column; }
  .chat-history { width:300px; border-right:1px solid #dee2e6; height:100vh; overflow-y:auto; background:#fff; flex-shrink:0; position:sticky; top:0; }
  .chat-panel { flex-grow:1; height:100vh; display:flex; flex-direction:column; flex-shrink:1; min-width:200px; overflow:hidden; }
  .chat-divider { border-bottom:1px solid #f1f3f5; padding-bottom:16px; margin-bottom:16px; }
  .message { max-width:75%; padding:12px 16px; border-radius:12px; word-break:break-word; }
  .message.user { background:#f1f3f5; color:#212529; }
  .message.dev { background:#0d6efd; color:#fff; margin-left:auto; }
  .ticket-info-bar { background:#fff; border-radius:16px; box-shadow:0 2px 8px rgba(0,0,0,0.03); margin:18px 18px 0 18px; padding:28px 32px; display:flex; align-items:center; justify-content:space-between; min-height:90px; border:1px solid #f1f3f5; }
  .ticket-info-bar .info-group { display:flex; gap:48px; align-items:center; }
  .ticket-info-bar .info-label { color:#6c757d; font-size:15px; font-weight:500; margin-bottom:2px; }
  .ticket-info-bar .info-value { color:#212529; font-size:21px; font-weight:700; margin-bottom:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .ticket-info-bar .info-title { font-size:20px; font-weight:600; color:#212529; margin-bottom:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .ticket-info-bar .status-badge { background:#FFEFEF; color:#7F1414; font-weight:600; font-size:16px; border-radius:18px; padding:6px 22px; display:inline-flex; align-items:center; gap:7px; }
  .submitter { display:flex; align-items:center; gap:12px; }
  .submitter-img { width:44px; height:44px; border-radius:50%; object-fit:cover; border:2px solid #f1f3f5; }
  .main-content { margin:0; height:100vh; overflow:hidden; display:flex; }
  .chat-image-clickable { max-width:250px; cursor:zoom-in; margin-top:8px; }
  .file-box { border:1px solid #dee2e6; background:#f8f9fa; padding:8px 14px; border-radius:8px; font-size:15px; display:flex; align-items:center; gap:6px; max-width:250px; margin-bottom:4px; word-break:break-all; overflow-wrap:anywhere; margin-top:16px!important; }
  .ticket-info-bar .submitter {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .ticket-info-bar .submitter-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f1f3f5;
  }
  .ticket-info-bar .submitter-details {
    display: flex;
    flex-direction: column;
  }
  .ticket-info-bar .submitter-name {
    font-weight: 600;
    font-size: 17px;
    color: #212529;
    margin-bottom: 0;
  }
  .ticket-info-bar .submitter-email {
    font-size: 15px;
    color: #6c757d;
    margin-bottom: 0;
  }
  @media (max-width:1300px){ .chat-history { display:block; position:absolute; z-index:1050; left:0; height:100vh; background:#fff; width:280px; box-shadow:2px 0 10px rgba(0,0,0,0.1); transform:translateX(-100%); transition:transform .2s ease;} .chat-history.show{transform:translateX(0);} #toggleChatHistory {display:inline-block;} }
  @media (min-width:1301px){ #toggleChatHistory { display:none !important; } }
</style>
@endsection

@section('content')
<div class="d-flex main-content">
  <div class="chat-history p-3">
    <h6 class="fw-bold mb-3">Chat History</h6>
    @foreach ($tickets as $ticket)
      @php
        $isActive = $ticket->id === $selectedTicket->id;
        $statusClass = match($ticket->status) {
            'Open' => 'bg-warning text-dark',
            'In Progress' => 'bg-primary',
            'Resolved' => 'bg-success',
            'Closed' => 'bg-danger',
            default => 'bg-secondary'
        };
      @endphp
      <a href="{{ route('ChatHistory.index', ['ticket' => $ticket->id]) }}" class="text-decoration-none text-dark">
        <div class="ticket-card mb-2 p-2 rounded {{ $isActive ? 'bg-light' : '' }}">
          <span class="badge {{ $statusClass }} badge-status mb-1">{{ $ticket->status }}</span>
          <div class="fw-semibold text-primary">TK-{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y') }}-{{ str_pad($ticket->id, 3, '0', STR_PAD_LEFT) }}</div>
          <div class="mb-1">{{ $ticket->title }}</div>
          @php
            $timezone = session('user_timezone', config('app.timezone'));
            $cTime = \Carbon\Carbon::parse($ticket->created_at)->timezone($timezone);
          @endphp
          <small class="text-muted">{{ $ticket->developer_name }} • {{ $cTime }}</small>
        </div>
      </a>
    @endforeach
  </div>

  <div class="chat-panel d-flex flex-column">
    <div class="ticket-info-bar">
      <div class="info-group">
        <div>
          <div class="info-label">Ticket ID</div>
          <div class="info-value">{{ 'TK-' . \Carbon\Carbon::parse($selectedTicket->created_at)->format('Y') . '-' . str_pad($selectedTicket->id, 3, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div>
          <div class="info-label">Judul</div>
          <div class="info-title">{{ $selectedTicket->title }}</div>
        </div>
        <div>
          <div class="info-label">Status</div>
          <span class="status-badge text-nowrap">
            <i class="bi bi-x-lg"></i>
            {{ $selectedTicket->status }}
          </span>
        </div>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="submitter">
          <img src="{{ $selectedTicket->support_avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($selectedTicket->support_name) }}" class="submitter-img" />
          <div class="submitter-details">
            <div class="submitter-name">{{ $selectedTicket->support_name }}</div>
            <div class="submitter-email">{{ $selectedTicket->support_email ?? '' }}</div>
          </div>
        </div>
        @if (!$note)
          <button class="btn btn-outline-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addNoteModal"><i class="bi bi-journal-plus me-1"></i>Tambah Note</button>
        @else
          <button class="btn btn-outline-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#viewNoteModal"><i class="bi bi-journal-text me-1"></i>Buka Note</button>
        @endif
        <button type="button" id="toggleChatHistory" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-list"></i></button>
      </div>
    </div>

    @php
      if (!function_exists('formatFileSize')) {
        function formatFileSize($bytes) {
          if ($bytes === 0) return '0 Bytes';
          $k = 1024;
          $sizes = ['Bytes', 'KB', 'MB', 'GB'];
          $i = floor(log($bytes, $k));
          return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
        }
      }
    @endphp

    <div class="chat-body" id="chatBody">
      @foreach ($selectedTicket->messages as $msg)
        @if ($msg['sender'] === 'support' )
        <div class="chat-divider" data-message-id="{{$msg['id']}}">
          <div class="d-flex">
            <img src="{{ $msg['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($selectedTicket->support_name) }}" class="rounded-circle me-2 mt-1" style="object-fit: cover;" width="32" height="32" />
            <div>
              @if (isset($msg['message']))
              <div class="message user mb-2" style="min-width:125px; max-width:300px;">{{ $msg['message'] }}</div>
              @endif
              @if(!empty($msg['attachments']))
                @foreach($msg['attachments'] as $att)
                  @php
                    $clientName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_/', '', $att['name']);
                    $clientSize = $att['size'] ? formatFileSize($att['size']) : 'Size N/A';
                  @endphp
                  @if($att['type'] === 'image')
                  <div class="mt-2">
                    <img src="{{ route('images.viewSupportChat', ['filename' => $att['name']]) }}" alt="{{ $att['name'] }}" class="img-thumbnail rounded shadow-sm chat-image-clickable" style="max-width: 250px; max-height: 200px; object-fit: cover;">
                  </div>
                  @else
                    <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block">
                      <i class="" data-filename="{{ $att['name'] }}"></i>
                      <div class="d-flex flex-column">
                        <a href="{{ route('files.downloadSupportChat', ['filename' => $att['name']]) }}" download="{{ $clientName }}" class="fw-semibold text-primary text-decoration-none">{{ $clientName }}</a>
                        <small class="text-muted">{{ $clientSize }}</small>
                      </div>
                    </div>
                  @endif
                @endforeach
              @endif
              <div class="text-small text-muted mt-1">{{ $msg['time'] }}</div>
            </div>
          </div>
        </div>
        @elseif ($msg['sender'] === 'dev')
        <div class="chat-divider text-end" data-message-id="{{$msg['id']}}">
          <div class="d-flex justify-content-end align-items-start">
            <div>
              @if (isset($msg['message']))
              <div class="message dev" style="min-width:125px; max-width:300px;">{{ $msg['message'] }}</div>
              @endif
              @if(!empty($msg['attachments']))
                @foreach($msg['attachments'] as $att)
                  @php
                    $supportSize = $att['size'] ? formatFileSize($att['size']) : 'Size N/A';
                    $supportName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_dev_\d+_/', '', $att['name']);
                  @endphp
                  @if($att['type'] === 'image')
                    <div class="mt-2 text-end">
                      <img src="{{ route('images.viewDev', ['filename' => $att['name']]) }}" alt="{{ $att['name'] }}" class="img-thumbnail rounded shadow-sm chat-image-clickable" style="max-width: 250px; max-height: 200px; object-fit: cover;">
                    </div>
                  @else
                      <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block">
                          <i class="" data-filename="{{ $att['name'] }}"></i>
                          <div class="d-flex flex-column">
                            <a href="{{ route('files.downloadDev', ['filename' => $att['name']]) }}" download="{{ $supportName }}" class="fw-semibold text-primary text-decoration-none">{{ $supportName }}</a>
                            <small class="text-muted">{{$supportSize}}</small>
                          </div>
                      </div>
                  @endif
                @endforeach
              @endif
              <div class="text-small text-muted mt-1 text-end">{{ $msg['time'] }}</div>
            </div>
            <img src="{{ $msg['avatar'] ?? asset('assetsadmin/img/avatars/default.png') }}" class="rounded-circle ms-2 mt-1" style="object-fit: cover;" width="32" height="32" />
          </div>
        </div>
        @endif
      @endforeach
    </div>
  </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('ChatHistory.note.store') }}">
        @csrf
        <input type="hidden" name="ticket_id" id="currentTicketID" value="{{ $selectedTicket->id }}">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-journal-plus me-2"></i>Tambah Note</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Note</label>
            <textarea name="note" class="form-control" rows="6" placeholder="Ketikkan note Anda..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Note</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Note Modal -->
@if($note)
<div class="modal fade" id="viewNoteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-journal-text me-2"></i>Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="border rounded p-3 bg-light" style="white-space:pre-wrap;">{{ $note->note }}</div>
      </div>
      <div class="modal-footer">
        <form method="POST" action="{{ route('ChatHistory.note.delete') }}" onsubmit="return confirm('Apakah Anda yakin untuk menghapus note ini?\nAksi tersebut tidak dapat dibalikkan!')" class="me-auto">
          @csrf
          <input type="hidden" name="ticket_id" value="{{ $selectedTicket->id }}">
          <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
        </form>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@section('scripts')
<script>
  const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
  fetch('/set-timezone', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      body: JSON.stringify({ timezone: userTimezone })
  });
  // Simple toggle for mobile sidebar (same behavior as chatdev)
  document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggleChatHistory');
    const historyPanel = document.querySelector('.chat-history');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => historyPanel.classList.toggle('show'));
    }

    // Image zoom modal (reuse logic inline) – optional placeholder
    document.querySelectorAll('.chat-image-clickable').forEach(img => {
      img.addEventListener('click', () => {
        const modal = document.createElement('div');
        modal.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-75';
        modal.style.cursor = 'zoom-out';
        modal.innerHTML = `<img src="${img.src}" class="img-fluid rounded shadow" style="max-height:90%;" />`;
        modal.addEventListener('click', () => modal.remove());
        document.body.appendChild(modal);
      });
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.file-box i[data-filename]').forEach(iconEl => {
        const filename = iconEl.getAttribute('data-filename');
        iconEl.className = getFileIcon(filename);
    });
  });
  function getFileIcon(filename) {
    if (!filename) return 'bi bi-file-earmark-arrow-down me-2';
    const ext = filename.split('.').pop().toLowerCase();
    if (ext === 'pdf') return 'bi bi-file-earmark-pdf pdf-icon me-2';
    if (ext === 'docx') return 'bi bi-file-earmark-word doc-icon me-2';
    if (ext === 'xlsx') return 'bi bi-file-earmark-excel excel-icon me-2';
    return 'bi bi-file-earmark-arrow-down me-2';
  }
</script>
@endsection
