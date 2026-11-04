@extends('Chatsup.layout.app')

@section('title', 'Chat Client')

@section('styles')
<script src="/js/format-chat.js" defer></script>
<style>
  .chat-body {
    padding: 20px;
    overflow-y: auto;
    flex-grow: 1;
    height: calc(100vh - 230px);
    background-color: #ffffff;
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    transition: background-color 0.3s ease;
  }

  .img-prev {
    width:96px;
    height:96px;
    object-fit: cover;
  }

  .chat-body.dragover {
    background-color: #ecf1ff;
    border: 2px dashed #0d6efd;
    position: relative;
  }
  .chat-body.dragover::after {
    content: 'Drop files here to upload';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(13, 110, 253, 0.9);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    z-index: 1000;
  }

  .chat-history {
    width: 300px;
    border-right: 1px solid #dee2e6;
    height: 100vh;
    overflow-y: auto;
    background-color: #fff;
  }

  .chat-history.transition-disabler {
    transition: none !important;
  }
  
  .chat-panel {
    flex-grow: 1;
    height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .chat-divider {
    border-bottom: 1px solid #f1f3f5;
    padding-bottom: 16px;
    margin-bottom: 16px;
  }

  .message {
    max-width: 75%;
    padding: 12px 16px;
    border-radius: 12px;
    word-break: break-word;
  }

  .message.client {
    background-color: #f1f3f5;
    color: #212529;
  }

  .message.support {
    background-color: #0d6efd;
    color: #fff;
    margin-left: auto;
  }
  
  .message.support a {
    color: #fff;
  }

  .chat-input-section {
    background-color: #fff;
    padding: 16px 20px 10px 20px;
    border-top: 1px solid #dee2e6;
    z-index: 200;
  }

  .chat-input-container {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    margin-bottom: 8px;
    position: relative;
  }

  .textarea-wrapper {
    flex: 1;
    position: relative;
    display: flex;
    align-items: flex-end;
  }

  .auto-resize-textarea {
    resize: none;
    overflow-y: hidden;
    min-height: 40px;
    max-height: 120px;
    transition: height 0.2s ease;
    padding: 8px 12px;
    line-height: 1.4;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    width: 100%;
    flex: 1;
  }

  .mark-resolved-btn {
    background-color: #20c997;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(32, 201, 151, 0.3);
    transition: all 0.3s ease;
  }

  .mark-resolved-btn:hover {
    background-color: #17b28c;
    box-shadow: 0 4px 6px rgba(23,178,140,0.4);
  }

  .btn.mark-resolved-btn:active {
    background-color: #17b28c;
  }

  .btn.mark-resolved-btn:disabled {
    background-color: #6D6969FF;
    opacity: 0.3;
    box-shadow: 0 2px 4px rgba(32, 32, 32, 0.3);
  }

  .chat-image-clickable {
    max-width: 250px;
    cursor: zoom-in;
    margin-top: 8px;
  }

  #selectedImageContainer {
    display: none;
    margin-bottom: 10px;
  }

  #selectedImage {
    max-width: 150px;
    height: auto;
  }

  .file-box {
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 6px;
    max-width: 250px;
    margin-bottom: 4px;
    word-break: break-all;
    overflow-wrap: anywhere;
    margin-top: 16px !important;
  }
  .file-box a {
    word-break: break-all;
    overflow-wrap: anywhere;
    display: inline-block;
    max-width: 180px;
    white-space: normal;
    text-overflow: ellipsis;
    overflow: hidden;
    vertical-align: middle;
  }

  .ticket-info-bar {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    margin: 18px 18px 0 18px;
    padding: 28px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 90px;
    border: 1px solid #f1f3f5;
  }
  .ticket-info-bar .info-group {
    display: flex;
    gap: 48px;
    align-items: center;
  }
  .ticket-info-bar .info-label {
    color: #6c757d;
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .ticket-info-bar .info-value {
    color: #212529;
    font-size: 21px;
    font-weight: 700;
    margin-bottom: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .ticket-info-bar .info-title {
    font-size: 20px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0;
    max-width: 240px;
  }
  .ticket-info-bar .status-badge {
    background: #e9f3ff;
    color: #2563eb;
    font-weight: 600;
    font-size: 16px;
    border-radius: 18px;
    padding: 6px 22px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    justify-content: center;
    flex-shrink: 0;
  }
  .ticket-info-bar .status-badge.chuu { /*SEDANG DIPROSES*/
    background: #FFFFEEFF;
    color: #8D6714FF;
  }
  .ticket-info-bar .status-badge.shuu { /*SELESAI*/
    background: #F1FFF0FF;
    color: #207513FF;
  }
  .ticket-info-bar .status-badge.tei { /*DITUTUP*/
    background: #FFEFEFFF;
    color: #7F1414FF;
  }
  .ticket-info-bar .status-badge .bi {
    font-size: 17px;
    vertical-align: middle;
  }
  .ticket-info-bar .client {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .ticket-info-bar .client-img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f1f3f5;
  }
  .ticket-info-bar .client-details {
    display: flex;
    flex-direction: column;
  }
  .ticket-info-bar .client-name {
    font-weight: 600;
    font-size: 17px;
    color: #212529;
    margin-bottom: 0;
  }
  .ticket-info-bar .client-email {
    font-size: 15px;
    color: #6c757d;
    margin-bottom: 0;
  }

  .main-content {
    margin: 0;
  }

  .pdf-icon {
    color: #d32f2f;
  }

  .doc-icon {
    color: #1976d2;
  }

  .excel-icon {
    color: #007329;
  }

 @media (max-width: 1300px) {
    .chat-history {
      display: block;
      position: absolute;
      z-index: 1050;
      left: 0;
      height: 100vh;
      background: #fff;
      width: 280px;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      transform: translateX(-100%);
      transition: transform 0.2s ease;
    }

    .chat-history.show {
      transform: translateX(0);
    }

    .main-content {
      margin-left: 0 !important;
      padding-left: 0 !important;
    }
  }
  @media (min-width: 1301px) {
    #toggleChatHistory {
      display: none !important;
    }
  }

  .ticket-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
    max-height: calc(1.4em * 2);
  }

  #submitButton.disabled {
    cursor: default;
    background-color: #D2D2D2;
    color: #7F7F7F;
    box-shadow: none;
    border-color: #D2D2D2;
  }

  .dots {
    display: none;
    padding: 8px;
    border-radius: 16px;
    background-color: #E5E5E5;
  }

  .dots.used {
    display: inline-flex;
  }

  .dots div {
    width: 8px;
    height: 8px;
    margin: 0 5px;
    background-color: #5D5D5D;
    border-radius: 50%;
  }

  @keyframes dotGrowing {
    0%, 80%, 100% { transform: scale(0.8); background-color: #979797;}
    40% { transform: scale(1.2); background-color: #7D7D7D;}
  }

  .meta-line {
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
  }

  .meta-line .name {
    max-width: 128px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex-shrink: 1;
  }

  .meta-line .time {
    flex-shrink: 0; 
  }
</style>
@endsection

@section('content')
  
<div class="d-flex main-content">
  <div class="chat-history p-3">
    <h6 class="fw-bold mb-3">Client Chat History</h6>
    @foreach ($tickets as $ticket)
      @php
        $isActive = $ticket->id === $selectedTicket->id;

        $statusClass = match($ticket->status) {
            'BELUM DIPROSES' => 'bg-primary',
            'SEDANG DIPROSES' => 'bg-warning text-dark',
            'DITUTUP' => 'bg-danger',
            'SELESAI' => 'bg-success',
            default => 'bg-secondary'
        };

        $statusTxt = match($ticket->status) {
          'BELUM DIPROSES' => 'Open',
          'SEDANG DIPROSES' => 'In Progress',
          'SELESAI' => 'Resolved',
          'DITUTUP' => 'Closed'
        };

        $selectedStatusClass = match($selectedTicket->status) {
          'BELUM DIPROSES' => ' ',
          'SEDANG DIPROSES' => 'chuu',
          'SELESAI' => 'shuu',
          'DITUTUP' => 'tei'
        };

        $selectedStatusIcon = match ($selectedTicket->status) {
          'BELUM DIPROSES' => 'bi-hourglass',
          'SEDANG DIPROSES' => 'bi-clock',
          'SELESAI' => 'bi-check2-circle',
          'DITUTUP' => 'bi-x-lg'
        };

        $selectedStatusTxt = match($selectedTicket->status) {
          'BELUM DIPROSES' => 'Open',
          'SEDANG DIPROSES' => 'In Progress',
          'SELESAI' => 'Resolved',
          'DITUTUP' => 'Closed'
        };
    @endphp

      <a href="{{ route('SupportClientChat.supportclientchat', ['ticket' => $ticket->id]) }}" class="text-decoration-none text-dark">
        <div class="ticket-card mb-2 p-2 rounded {{ $isActive ? 'bg-light' : '' }}" id="tc-{{$ticket->id}}">
          <span class="badge {{ $statusClass }} badge-status mb-1">{{ $statusTxt }}</span>
          <div class="fw-semibold text-primary">SS-{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y') }}-{{ str_pad($ticket->id, 3, '0', STR_PAD_LEFT) }}</div>
          <div class="mb-1 ticket-title">{{ $ticket->title }}</div>
          @php
            $timezone = session('user_timezone', config('app.timezone'));
            $cTime = \Carbon\Carbon::parse($ticket->latest_chat_time ?? $ticket->created_at)->timezone($timezone);
          @endphp
          <small class="text-muted meta-line">
            <span class="name">{{ $ticket->client_name }}</span>
            <span>•</span>
            <span class="time">{{ $cTime }}</span>
          </small>
        </div>
      </a>
    @endforeach
  </div>

  <div class="chat-panel d-flex flex-column">
    <!-- Ticket Info Top Bar -->
    <div class="ticket-info-bar">
      <div class="info-group">
        <div>
          <div class="info-label">Ticket ID</div>
          <div class="info-value">{{ "SS-" . \Carbon\Carbon::parse($selectedTicket->created_at)->format('Y') . "-" . str_pad($selectedTicket->id, 3, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div>
          <div class="info-label">Judul</div>
          <div class="info-title">{{ $selectedTicket->title }}</div>
        </div>
      </div>
      <div class="client">
        <div>
          <div class="info-label">Status</div>
          <span class="text-nowrap status-badge {{ $selectedStatusClass }}">
            <i class="bi {{ $selectedStatusIcon }}"></i>
            {{ $selectedStatusTxt }}
          </span>
        </div>
        <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($selectedTicket->client_name) }}" class="client-img" />
        <div class="client-details">
          <div class="client-name text-nowrap">{{ $selectedTicket->client_name }}</div>
          <div class="client-email text-nowrap">{{ $selectedTicket->client_email ?? '' }}</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary ms-3 text-nowrap" data-bs-toggle="modal" data-bs-target="#createDevTicketModal">
          <i class="bi bi-journal-plus me-1"></i> Dev Ticket
        </button>
      </div>
    </div>
    <!-- End Ticket Info Top Bar -->

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
        @if ($msg['sender'] === 'client')
        <div class="chat-divider" data-chat-id="{{$msg['id']}}">
            <div class="d-flex">
                <img src="{{ $selectedTicket->client_avatar ? route('users', ['filename' => $selectedTicket->client_avatar]) : 'https://ui-avatars.com/api/?name=' . urlencode($selectedTicket->client_name) }}" class="rounded-circle me-2 mt-1 object-fit-cover" width="32" height="32" />
                <div>
                    @if (isset($msg['message']))
                      <div class="message client mb-2" style="min-width: 180px; max-width: 300px;">
                          {{ $msg['message'] }}
                      </div>
                    @endif
                    @if(!empty($msg['attachments']))
                        @foreach($msg['attachments'] as $att)
                          @php
                            $clientName = preg_replace('/^\d+_/', '', $att['name']);
                            $clientSize = $att['size'] ? formatFileSize($att['size']) : 'Size N/A';
                          @endphp
                          @if($att['type'] === 'image')
                          <div class="mt-2">
                            <img src="{{ route('images.view', ['filename' => $att['name']]) }}" alt="{{ $att['name'] }}" class="img-thumbnail rounded shadow-sm chat-image-clickable" style="width: {{ $att['width'] ?? 250 }}px; height: {{ $att['height'] ?? 200 }}px; object-fit: cover;">
                          </div>
                          @else
                            <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block">
                              <i class="" data-filename="{{ $att['name'] }}"></i>
                              <div class="d-flex flex-column">
                                <a href="{{ route('files.download', ['filename' => $att['name']]) }}" download="{{ $clientName }}" class="fw-semibold text-primary text-decoration-none">{{ $clientName }}</a>
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
        @elseif ($msg['sender'] === 'support')
        <div class="chat-divider text-end" data-chat-id="{{$msg['id']}}">
            <div class="d-flex justify-content-end align-items-start">
                <div>
                    @if (isset($msg['message']))
                      <div class="message support" style="min-width: 180px; max-width: 300px;">
                          {{ $msg['message'] }}
                      </div>
                    @endif
                    @if(!empty($msg['attachments']))
                      @foreach($msg['attachments'] as $att)
                        @php
                          $supportSize = $att['size'] ? formatFileSize($att['size']) : 'Size N/A';
                          $supportName = preg_replace('/^[A-Za-z]{2}-\d{8}-\d+_\d+_[1-4]_/', '', $att['name']);
                        @endphp
                        @if($att['type'] === 'image')
                          <div class="mt-2 text-end">
                            <img src="{{ route('images.viewSupport', ['filename' => $att['name']]) }}" alt="{{ $att['name'] }}" class="img-thumbnail rounded shadow-sm chat-image-clickable" style="width: {{ $att['width'] ?? 32 }}px; height: {{ $att['height'] ?? 32 }}px; object-fit: cover;">
                          </div>
                        @else
                            <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block">
                                <i class="" data-filename="{{ $att['name'] }}"></i>
                                <div class="d-flex flex-column">
                                  <a href="{{ route('files.downloadSupport', ['filename' => $att['name']]) }}" download="{{ $supportName }}" class="fw-semibold text-primary text-decoration-none">{{ $supportName }}</a>
                                  <small class="text-muted">{{$supportSize}}</small>
                                </div>
                            </div>
                        @endif
                      @endforeach
                    @endif
                    <div class="text-small text-muted mt-1 text-end">
                      <span class="send-indicator me-1 {{ ($msg['status'] ?? '') === 'read' ? 'd-none' : 'd-inline-block' }}">Sent</span>
                      <span class="read-indicator me-1 {{ ($msg['status'] ?? '') === 'read' ? 'd-inline-block' : 'd-none' }}">Read</span>
                      {{ $msg['time'] }}
                    </div>
                </div>
                <img src="{{ $msg['avatar'] ?? asset('assetsadmin/img/avatars/default.png') }}" class="rounded-circle ms-2 mt-1 object-fit-cover" width="32" height="32" />
            </div>
        </div>
        @elseif ($msg['sender'] === 'system')
        <div class="chat-divider text-center">
            <div class="d-flex justify-content-center">
                <div class="message system">
                    {{ $msg['message'] }}
                </div>
            </div>
        </div>
        @endif
      @endforeach
      <div class="dots">
        <div style="animation: dotGrowing 1s infinite ease-in-out;"></div>
        <div style="animation: dotGrowing 1s infinite ease-in-out 0.2s;"></div>
        <div style="animation: dotGrowing 1s infinite ease-in-out 0.4s;"></div>
      </div>
      
    </div>
    <div class='chat-input-section'>
      <form method="POST" id="ticket-resolver" action="{{ route('SupportClientChat.resolve', ['id' => $selectedTicket->id]) }}" onsubmit="return confirm('Yakin ingin menandai ticket ini sebagai selesai?')">
        @csrf
      </form>
      <form id="chat-form" action="{{ route('SupportClientChat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="ticket" id="currentTicketID" value="{{ $selectedTicket->id }}">

        <!-- Image Preview -->
        <div id="selectedImageContainer" class="mb-2">
          <img id="selectedImage" src="" alt="Selected Image" class="img-thumbnail img-prev">
          <button type="button" id="removeImage" class="btn btn-sm btn-danger">x</button>
        </div>

        <!-- File Preview -->
        <div id="selectedFileContainer" class="mb-2" style="display:none;">
          <div class="file-box">
            <i class="bi bi-file-earmark-arrow-down me-2"></i>
            <span id="selectedFileName"></span>
            <button type="button" id="removeFile" class="btn btn-sm btn-danger ms-2">x</button>
          </div>
        </div>

          <div class="chat-input-container">
            <div class="textarea-wrapper">
              <textarea class="form-control auto-resize-textarea" name="message" id="messageTextarea" placeholder="{{$selectedTicket->status === 'DITUTUP' ? "Anda tidak dapat chat pada tiket yang sudah ditutup." : "Ketik Pesan..." }}" @if($selectedTicket->status === 'DITUTUP') disabled @endif></textarea>
            </div>

            <button type="submit" id="submitButton" class="btn btn-primary rounded px-3 py-2 disabled"><i class="bi bi-send-fill"></i></button>

            <button type="button" id="resolverButton" onclick="manualTrigger(event, 'ticket-resolver')" @if($selectedTicket->status === 'DITUTUP' || $selectedTicket->status === 'SELESAI') disabled @endif class="btn d-flex align-items-center gap-2 text-white mark-resolved-btn">
              <i class="bi bi-check-circle-fill fs-5"></i>
              <span class="fw-semibold">Mark as Resolved</span>
            </button>
          </div>

        <div class="d-flex gap-2 pt-1">
          <input type="file" name="attachment[]" id="attachment" class="d-none" accept=".docx,.pdf,.xlsx" multiple disabled>
          <button type="button" class="btn btn-sm btn-outline-secondary" @if($selectedTicket->status !== 'DITUTUP') onclick="document.getElementById('attachment').click();" @endif>
            <i class="bi bi-paperclip"></i> File
          </button>

          <input type="file" name="image[]" id="image" class="d-none" accept=".jpg,.jpeg,.webp,.png" multiple disabled>
          <button type="button" class="btn btn-sm btn-outline-secondary" @if($selectedTicket->status !== 'DITUTUP') onclick="document.getElementById('image').click();" @endif>
            <i class="bi bi-image"></i> Gambar
          </button>

          <button type="button" id="toggleChatHistory" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list"></i> Chat History
          </button>
        </div>
      </form>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
          <img id="modalImage" src="" class="img-fluid rounded shadow" />
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Global Create Dev Ticket Modal (single instance) -->
<div class="modal fade" id="createDevTicketModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('SupportClientChat.createDevTicket') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="case_type" value="ticket" />
        <input type="hidden" name="client_ticket_id" value="{{ $selectedTicket->id }}" />
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-journal-plus me-2"></i>Buat Tiket Dev</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Judul <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" maxlength="255" required placeholder="Judul Ticket" oninput="validateTitleInput(this);"/>
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
            <textarea name="description" id="description" class="form-control" rows="6" required maxlength="10000" placeholder="Deskripsi issue untuk developer..."></textarea>
          </div>
          <div id="description-word-count">
            0 / 10000
          </div>
          <span id="description-error" style="color: red; font-size: 13px; display: none;">Deskripsi tersebut terlalu panjang</span>
          <div class="mb-3">
            <label class="form-label">Attachment (opsional)</label>
            <div class="d-flex align-items-center">
              <input type="file" id="devTicketImageInput" name="image" accept=".jpg,.jpeg,.png,.webp,.xlsx,.docx,.pdf" class="form-control w-75"/>          
              <div class="mx-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="removeAttachmentBtn">
                  <i class="bi bi-x-circle me-1"></i> Hapus
                </button>
              </div>
            </div>
            <small class="text-muted">Max 5MB. JPG/PNG/WEBP/DOCX/PDF/XLSX.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Buat</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
@include('partials.fileverifier')
<script>

  //Timezone

  const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

  fetch('/set-timezone', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      body: JSON.stringify({ timezone: userTimezone })
  });

// Deklarasi Variabel Global
  let selectedImages = [];
  let selectedAtts = [];
  const allowedImages = ['png', 'jpg', 'jpeg', 'webp'];
  const allowedAtts = ['docx', 'pdf','xlsx'];
  const maxSize = 10 * 1024 * 1024;
  const maxFiles = 4;

  function getExt(s){
    return s.split('.').pop().toLowerCase();
  }

  async function allowedIMG(f){
    if (!f) return false;
    const ext = getExt(f.name);
    if (!allowedImages.includes(ext)) return false;
    if (f.size > maxSize) return false;
    if (f.name.length > 191) return false;

    const sig = await getFileSignature(f);

    if (["jpg", "jpeg"].includes(ext)) {
        return sig === "ffd8ffe0" || sig === "ffd8ffe1" || sig === "ffd8ffe2";
    }
    if (ext === "png") {
        return sig === "89504e47";
    }
    if (ext === "webp") {
        return await isWebP(f);
    }

    return false;
  }

  async function allowedATT(f){
    const ext = getExt(f.name);
    if (!allowedAtts.includes(ext)) return false;
    if (f.size > maxSize) return false;
    if (f.name.length > 191) return false;

    const sig = await getFileSignature(f);

    if (ext === "pdf") {
        return sig === "25504446";
    }
    if (ext === "docx") { //Apparently a Docx is a zip so... extra precautions ahead!
        return await isDocx(f);
    }
    if (ext === "xlsx") {
        return await isXlsx(f);
    }

    return false;
  }
</script>
<script src="{{ asset('js/chatdev-min.js') }}" defer></script>
<script>
  // Enable Enter-to-send for client chat (no realtime)
  document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.getElementById('messageTextarea');
    const form = document.getElementById('chat-form');
    if (textarea && form) {
      textarea.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          if (form.dataset.submitting === 'true') return;
          form.dataset.submitting = 'true';
          // Use requestSubmit to trigger submit handlers (file attach, etc.)
          if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
          } else {
            form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
          }
          setTimeout(() => { form.dataset.submitting = 'false'; }, 1000);
        }
      });
    }

    validateDevTicketCredentials();
  });

  function validateTitleInput(inputElement) {
    const inputValue = inputElement.value;
    const forbiddenCharacters = /[^A-Za-z0-9\s!@#\$%\^&\*\(\)\-\_\=\+\[\]\{\};:'",.<>\/\?\\\|`~]/g;

    if (forbiddenCharacters.test(inputValue)) {
      inputElement.value = inputValue.replace(forbiddenCharacters, '');
    }
  }

  const devTicketTitle = document.querySelector('#createDevTicketModal input[type="text"]');
  const devTicketDescription = document.querySelector('#createDevTicketModal textarea');
  const devTicketSubmitBtn = document.querySelector('#createDevTicketModal button[type="submit"]');
  const devTicketInputImage = document.querySelector('#createDevTicketModal input[type="file"]')
  const removeAttachmentBtn = document.getElementById('removeAttachmentBtn');
  const wordCountDisplay = document.getElementById('description-word-count');
  const descriptionError = document.getElementById('description-error');
  const charLimit = 10000;

  function validateDevTicketCredentials() {
    const title = devTicketTitle.value;
    const description = devTicketDescription.value;
    const forbiddenCharacters = /[^A-Za-z0-9\s!@#\$%\^&\*\(\)\-\_\=\+\[\]\{\};:'",.<>\/\?\\\|`~]/g;
    if (title.length > 0 && title.length <= 255 && description.length > 0 && description.length <= 10000 && !forbiddenCharacters.test(title) && !devTicketSubmitBtn.getAttribute("data-bs-dismiss"))
    devTicketSubmitBtn.setAttribute("data-bs-dismiss", "modal");

    if (devTicketSubmitBtn.getAttribute("data-bs-dismiss") && ((description.length <= 0 || description.length > 10000) || forbiddenCharacters.test(title) || !(title.length > 0 && title.length <= 255))) {
      devTicketSubmitBtn.removeAttribute("data-bs-dismiss");
    }
  }

  devTicketTitle.addEventListener('input', function(input) {
    validateTitleInput(input);
    validateDevTicketCredentials();
  });

  devTicketDescription.addEventListener('input', function(input) {
    validateDevTicketCredentials();

    const count = devTicketDescription.value.length;

    wordCountDisplay.textContent = `${count} / ${charLimit}`;

    if (count > charLimit) {
        devTicketDescription.classList.add('is-invalid');
        descriptionError.style.display = 'block';
    } else {
        devTicketDescription.classList.remove('is-invalid');
        descriptionError.style.display = 'none';
    }
  });

  document.querySelector('#createDevTicketModal form').addEventListener('submit', function(e) {
    devTicketSubmitBtn.disabled = true;
  });

  let lastValidFile = null;
  let invoked = false;

  async function handleFile(element, file) {
    if (!file) {
      if (lastValidFile) {
        const dt = new DataTransfer();
        dt.items.add(lastValidFile);
        element.files = dt.files;
        if(!invoked) element.dispatchEvent(new Event("change", { bubbles: true }));
      } else {
        element.value = "";
      }
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      if (lastValidFile) {
        const dt = new DataTransfer();
        dt.items.add(lastValidFile);
        element.files = dt.files;
        if(!invoked) element.dispatchEvent(new Event("change", { bubbles: true }));
      } else {
        element.value = "";
      }
      return;
    }

    const allowed = await allowedIMG(file) || await allowedATT(file);
    if (!allowed) {
      if (lastValidFile) {
        const dt = new DataTransfer();
        dt.items.add(lastValidFile);
        element.files = dt.files;
        if(!invoked) element.dispatchEvent(new Event("change", { bubbles: true }));
      } else {
        element.value = "";
      }
      return;
    }

    lastValidFile = file;

    const dt = new DataTransfer();
    dt.items.add(file);
    element.files = dt.files;
    if(!invoked) element.dispatchEvent(new Event("change", { bubbles: true }));
  }

devTicketInputImage.addEventListener('change', async function (e) {
  const file = e.target.files && e.target.files[0];
  await handleFile(devTicketInputImage, file);
  invoked = true;
});

devTicketInputImage.addEventListener('drop', async function (e) {
  e.preventDefault();
  const file = e.dataTransfer.files && e.dataTransfer.files[0];
  await handleFile(devTicketInputImage, file);
  invoked = true;
});
if (removeAttachmentBtn && devTicketInputImage) {
  removeAttachmentBtn.addEventListener('click', function () {
    lastValidFile = null;
    invoked = false;

    devTicketInputImage.value = '';

    devTicketInputImage.dispatchEvent(
      new Event('change', { bubbles: true })
    );
  });
}
</script>
@include('Chatsup.partials.customrequest')
@include('Chatsup.partials.firebase')
@endsection 