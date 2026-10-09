@extends('layout.app')

@section('title', 'Chat Ticket')

@section('styles')
    <script src="/js/format-chat.js" defer></script>
    <style>
        .chat-body {
            padding: 20px;
            overflow-y: auto;
            flex-grow: 1;
            background-color: #ffffff;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 0;
            min-height: 0;
            flex-direction: column;
            transition: background-color 0.3s ease;
        }

        .img-prev {
            width: 96px;
            height: 96px;
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
            flex-shrink: 0;
            position: sticky;
            top: 0;
        }

        .chat-history.transition-disabler {
            transition: none !important;
        }

        .chat-panel {
            flex-grow: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            flex-shrink: 1;
            min-width: 200px;
            overflow: hidden;
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

        .message.user {
            background-color: #f1f3f5;
            color: #212529;
        }

        .message.dev {
            background-color: #0d6efd;
            color: #fff;
            margin-left: auto;
        }

        .message.dev a {
            color: #fff;
        }

        .chat-input-section {
            background-color: #fff;
            padding: 16px 20px 10px 20px;
            border-top: 1px solid #dee2e6;
            position: sticky;
            bottom: 0;
            z-index: 10;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            margin-top: auto;
            min-height: 0;
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
            box-shadow: 0 4px 6px rgba(23, 178, 140, 0.4);
        }

        .btn.mark-resolved-btn:active {
            background-color: #17b28c;
        }

        .btn.mark-resolved-btn:disabled {
            background-color: #6D6969FF;
            opacity: 0.3;
            box-shadow: 0 2px 4px rgba(32, 32, 32, 0.3);
        }

        .undo-btn {
            background-color: #dc3545;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
        }

        .undo-btn:hover {
            background-color: #bb2d3b;
            /* darker shade */
            box-shadow: 0 4px 6px rgba(220, 53, 69, 0.4);
        }

        .btn.undo-btn:active {
            background-color: #a52834;
            /* even darker */
        }

        .btn.undo-btn:disabled {
            background-color: #6D6969FF;
            opacity: 0.4;
            box-shadow: none;
        }

        .reopen-progress-btn {
    background-color: #0d6efd;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
    transition: all 0.3s ease;
}

.reopen-progress-btn:hover {
    background-color: #0b5ed7;
    box-shadow: 0 4px 6px rgba(11, 94, 215, 0.4);
}

.reopen-new-btn {
    background-color: #fd7e14;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(253, 126, 20, 0.3);
    transition: all 0.3s ease;
}

.reopen-new-btn:hover {
    background-color: #dc6a02;
    box-shadow: 0 4px 6px rgba(220, 106, 2, 0.4);
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

        .pdf-icon {
            color: #d32f2f;
        }

        .doc-icon {
            color: #1976d2;
        }

        .excel-icon {
            color: #007329;
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
            /* <-- Tambahkan ini */
            overflow-wrap: anywhere;
            /* <-- Tambahkan ini */
            margin-top: 16px !important;
        }

        .file-box a {
            word-break: break-all;
            /* <-- Tambahkan ini */
            overflow-wrap: anywhere;
            /* <-- Tambahkan ini */
            display: inline-block;
            /* <-- Tambahkan ini */
            max-width: 180px;
            /* <-- Tambahkan ini, agar link tidak melebar */
            white-space: normal;
            /* <-- Tambahkan ini */
            text-overflow: ellipsis;
            /* <-- Tambahkan ini */
            overflow: hidden;
            /* <-- Tambahkan ini */
            vertical-align: middle;
        }

        .ticket-info-bar {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            margin: 18px 18px 0 18px;
            padding: 28px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 90px;
            border: 1px solid #f1f3f5;
            flex-shrink: 0;
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
        }

        .ticket-info-bar .info-value {
            color: #212529;
            font-size: 21px;
            font-weight: 700;
            margin-bottom: 0;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
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
        }

        .ticket-info-bar .status-badge.chuu {
            /*SEDANG DIPROSES*/
            background: #FFFFEEFF;
            color: #8D6714FF;
        }

        .ticket-info-bar .status-badge.shuu {
            /*SELESAI*/
            background: #F1FFF0FF;
            color: #207513FF;
        }

        .ticket-info-bar .status-badge.tei {
            /*DITUTUP*/
            background: #FFEFEFFF;
            color: #7F1414FF;
        }

        .ticket-info-bar .status-badge .bi {
            font-size: 17px;
            vertical-align: middle;
        }

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

        .main-content {
            margin: 0;
            height: 100vh;
            overflow: hidden;
            display: flex;
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
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
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

        .ticket-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4;
            max-height: calc(1.4em * 2);
        }

        @keyframes dotGrowing {

            0%,
            80%,
            100% {
                transform: scale(0.8);
                background-color: #979797;
            }

            40% {
                transform: scale(1.2);
                background-color: #7D7D7D;
            }
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
    @if (session('success'))
        <div class="alert alert-success py-2 mb-0">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2 mb-0">{{ session('error') }}</div>
    @endif
    <div class="d-flex main-content">
        <div class="chat-history p-3" data-status-order="In Progress,Resolved">
            <h6 class="fw-bold mb-3">Chat History</h6>
            @foreach ($tickets as $ticket)
                @php
                    $isActive = $ticket->id === $selectedTicket->id;

                    $statusClass = match ($ticket->status) {
                        'In Progress' => 'bg-warning text-dark',
                        'Open' => 'bg-primary',
                        'Resolved' => 'bg-success',
                        'Closed' => 'bg-danger',
                        default => 'bg-secondary',
                    };

                    $selectedStatusClass = match ($selectedTicket->status) {
                        'Open' => '',
                        'In Progress' => 'chuu',
                        'Resolved' => 'shuu',
                        'Closed' => 'tei',
                    };

                    $selectedStatusIcon = match ($selectedTicket->status) {
                        'Open' => 'bi-hourglass',
                        'In Progress' => 'bi-clock',
                        'Resolved' => 'bi-check2-circle',
                        'Closed' => 'bi-x-lg',
                    };
                @endphp

                <a href="{{ route('Chatdev.chatdev', ['ticket' => $ticket->id]) }}" class="text-decoration-none text-dark">
                    <div class="ticket-card mb-2 p-2 rounded {{ $isActive ? 'bg-light' : '' }}" id="tc-{{ $ticket->id }}"
                        data-priority="{{ $ticket->priority }}"
                        data-latest="{{ \Carbon\Carbon::parse($ticket->latest_chat_time ?? $ticket->created_at)->getTimestampMs() }}">
                        <span class="badge {{ $statusClass }} badge-status mb-1">{{ $ticket->status }}</span>
                        <div class="fw-semibold text-primary">
                            TK-{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y') }}-{{ str_pad($ticket->id, 3, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="mb-1 ticket-title">{{ $ticket->title }}</div>
                        @php
                            $timezone = session('user_timezone', config('app.timezone'));
                            $cTime = \Carbon\Carbon::parse($ticket->latest_chat_time ?? $ticket->created_at)->timezone($timezone);
                        @endphp
                        <small class="text-muted meta-line">
                            <span class="name">{{ $ticket->developer_name }}</span>
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
                        <div class="info-value">
                            {{ 'TK-' . \Carbon\Carbon::parse($selectedTicket->created_at)->format('Y') . '-' . str_pad($selectedTicket->id, 3, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Judul</div>
                        <div class="info-title">{{ $selectedTicket->title }}</div>
                    </div>

                </div>
                <div class="submitter">
                    <div>
                        <div class="info-label">Status</div>
                        <span class="text-nowrap status-badge {{ $selectedStatusClass }}">
                            <i class="bi {{ $selectedStatusIcon }}"></i>
                            {{ $selectedTicket->status }}
                        </span>
                    </div>
                    <img src="{{ $selectedTicket->support_avatar ?? asset('assetsadmin/img/avatars/default.png') }}"
                        class="submitter-img" />
                    <div class="submitter-details">
                        <div class="submitter-name text-nowrap">{{ $selectedTicket->support_name }}</div>
                        <div class="submitter-email text-nowrap">{{ $selectedTicket->support_email ?? '' }}</div>
                    </div>
                </div>
            </div>
            <!-- End Ticket Info Top Bar -->

            @php
                if (!function_exists('formatFileSize')) {
                    function formatFileSize($bytes)
                    {
                        if ($bytes === 0) {
                            return '0 Bytes';
                        }
                        $k = 1024;
                        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
                        $i = floor(log($bytes, $k));
                        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
                    }
                }
            @endphp

            <div class="chat-body" id="chatBody">
                @foreach ($selectedTicket->messages as $msg)
                    @if ($msg['sender'] === 'support')
                        <div class="chat-divider" data-message-id="{{ $msg['id'] }}">
                            <div class="d-flex">
                                <img src="{{ $msg['avatar'] ?? asset('assetsadmin/img/avatars/default.png') }}"
                                    class="rounded-circle me-2 mt-1 object-fit-cover chat-avatar" width="32"
                                    height="32" />
                                <div>
                                    @if (isset($msg['message']))
                                        <div class="message user mb-2" style="min-width: 180px; max-width: 300px;">
                                            {{ $msg['message'] }}
                                        </div>
                                    @endif
                                    @if (!empty($msg['attachments']))
                                        @foreach ($msg['attachments'] as $att)
                                            @php
                                                $clientName = preg_replace(
                                                    '/^[A-Za-z]{2}-\d{8}-\d+_\d+_/',
                                                    '',
                                                    $att['name'],
                                                );
                                                $clientSize = $att['size'] ? formatFileSize($att['size']) : 'Size N/A';
                                            @endphp
                                            @if ($att['type'] === 'image')
                                                <div class="mt-2">
                                                    <img src="{{ route('images.viewSupportChat', ['filename' => $att['name']]) }}"
                                                        alt="{{ $clientName }}"
                                                        class="img-thumbnail rounded shadow-sm chat-image-clickable"
                                                        style="width: {{ $att['width'] ?? 32 }}px; height: {{ $att['height'] ?? 32 }}px; object-fit: cover;">
                                                </div>
                                            @else
                                                <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block">
                                                    <i class="" data-filename="{{ $att['name'] }}"></i>
                                                    <div class="d-flex flex-column">
                                                        <a href="{{ route('files.downloadSupportChat', ['filename' => $att['name']]) }}"
                                                            download="{{ $clientName }}"
                                                            class="fw-semibold text-primary text-decoration-none">{{ $clientName }}</a>
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
                        <div class="chat-divider text-end" data-message-id="{{ $msg['id'] }}">
                            <div class="d-flex justify-content-end align-items-start">
                                <div>
                                    @if (isset($msg['message']))
                                        <div class="message dev" style="min-width: 180px; max-width: 300px;">
                                            {{ $msg['message'] }}
                                        </div>
                                    @endif
                                    @if (!empty($msg['attachments']))
                                        @foreach ($msg['attachments'] as $att)
                                            @php
                                                $supportSize = $att['size'] ? formatFileSize($att['size']) : 'Size N/A';
                                                $supportName = preg_replace(
                                                    '/^[A-Za-z]{2}-\d{8}-\d+_\d+_dev_\d+_/',
                                                    '',
                                                    $att['name'],
                                                );
                                            @endphp
                                            @if ($att['type'] === 'image')
                                                <div class="mt-2 text-end">
                                                    <img src="{{ route('images.viewDev', ['filename' => $att['name']]) }}"
                                                        alt="{{ $supportName }}"
                                                        class="img-thumbnail rounded shadow-sm chat-image-clickable"
                                                        style="width: {{ $att['width'] ?? 250 }}px; height: {{ $att['height'] ?? 200 }}px; object-fit: cover;">
                                                </div>
                                            @else
                                                <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block">
                                                    <i class="" data-filename="{{ $att['name'] }}"></i>
                                                    <div class="d-flex flex-column">
                                                        <a href="{{ route('files.downloadDev', ['filename' => $att['name']]) }}"
                                                            download="{{ $supportName }}"
                                                            class="fw-semibold text-primary text-decoration-none">{{ $supportName }}</a>
                                                        <small class="text-muted">{{ $supportSize }}</small>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                    <div class="text-small text-muted mt-1 text-end">
                                        <span
                                            class="send-indicator me-1 {{ ($msg['status'] ?? '') === 'read' ? 'd-none' : 'd-inline-block' }}">Sent</span>
                                        <span
                                            class="read-indicator me-1 {{ ($msg['status'] ?? '') === 'read' ? 'd-inline-block' : 'd-none' }}">Read</span>
                                        {{ $msg['time'] }}
                                    </div>
                                </div>
                                <img src="{{ $msg['avatar'] ?? asset('assetsadmin/img/avatars/default.png') }}"
                                    class="rounded-circle ms-2 mt-1 object-fit-cover chat-avatar" width="32"
                                    height="32" />
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
            <div class="chat-input-section">
                <form method="POST" id="ticket-resolver"
                    action="{{ route('Chatdev.resolve', ['id' => $selectedTicket->id]) }}"
                    onsubmit="return confirm('Apakah Anda yakin untuk menandai ticket ini sebagai selesai?')">
                    @csrf
                </form>
                <form method="POST" id="undo-ticket" action="{{ route('Chatdev.undo', ['id' => $selectedTicket->id]) }}"
                    onsubmit="return confirm('Apakah Anda yakin untuk batal mengambil tiket ini?')">
                    @csrf
                </form>
                <form method="POST" id="reopen-inprogress-ticket"
                    action="{{ route('Chatdev.reopen', ['id' => $selectedTicket->id]) }}"
                    onsubmit="return confirm('Buka kembali tiket ini dan lanjutkan sebagai In Progress? Gunakan ini jika masih ada bug lanjutan yang perlu Anda kerjakan.')">
                    @csrf
                    <input type="hidden" name="target_status" value="In Progress">
                </form>
                <form method="POST" id="reopen-new-ticket"
                    action="{{ route('Chatdev.reopen', ['id' => $selectedTicket->id]) }}"
                    onsubmit="return confirm('Buka kembali tiket ini sebagai tiket baru (Open)? Tiket akan dilepas dari Anda dan kembali ke antrian.')">
                    @csrf
                    <input type="hidden" name="target_status" value="Open">
                </form>
                @php $chatLocked = in_array(trim($selectedTicket->status), ['Closed', 'Resolved']); @endphp
                <form id="chat-form" action="{{ route('Chatdev.store') }}" method="POST" enctype="multipart/form-data"
                    class="w-100">
                    @csrf
                    <input type="hidden" name="ticket" id="currentTicketID" value="{{ $selectedTicket->id }}">

                    <!-- Preview Area (stacked above row if visible) -->
                    <div id="selectedImageContainer" class="mb-2">
                        <img id="selectedImage" src="" alt="Selected Image" class="img-thumbnail img-prev">
                        <button type="button" id="removeImage" class="btn btn-sm btn-danger">x</button>
                    </div>
                    <div id="selectedFileContainer" class="mb-2" style="display:none;">
                        <div class="file-box">
                            <i class="bi bi-file-earmark-arrow-down me-2"></i>
                            <span id="selectedFileName"></span>
                            <button type="button" id="removeFile" class="btn btn-sm btn-danger ms-2">x</button>
                        </div>
                    </div>

                    <!-- Row 1: textarea + send + resolve -->
                    <div class="d-flex align-items-start gap-2 chat-input-row">
                        <div class="flex-grow-1 position-relative">
                            <textarea class="form-control auto-resize-textarea" name="message" @if ($chatLocked) disabled @endif
                                id="messageTextarea"
                                placeholder="{{ $selectedTicket->status === 'Closed' ? 'Anda tidak dapat chat pada tiket yang sudah ditutup.' : ($chatLocked ? 'Tiket sudah Resolved. Klik Reopen untuk melanjutkan chat.' : 'Ketik Pesan...') }}"></textarea>
                        </div>
                        <button type="submit" id="submitButton" class="btn btn-primary px-3 py-2 disabled"
                            style="height:40px;">
                            <i class="bi bi-send-fill"></i>
                        </button>
                        <div class="d-flex flex-column gap-2">
    @if (trim($selectedTicket->status) === 'Resolved')
        {{-- Tiket sudah Resolved: sediakan opsi reopen jika ternyata masih ada
             bug lanjutan atas case yang sudah diselesaikan. --}}
        <button type="button" onclick="manualTrigger(event, 'reopen-inprogress-ticket')"
            class="btn d-flex align-items-center gap-2 text-white reopen-progress-btn"
            title="Masih ada bug lanjutan, lanjutkan pengerjaan tiket ini" style="height:40px;">
            <i class="bi bi-arrow-repeat fs-5"></i>
            <span class="fw-semibold d-none d-sm-inline">Reopen (In Progress)</span>
        </button>
        <button type="button" onclick="manualTrigger(event, 'reopen-new-ticket')"
            class="btn d-flex align-items-center gap-2 text-white reopen-new-btn"
            title="Lepas tiket ini kembali ke antrian sebagai tiket baru" style="height:40px;">
            <i class="bi bi-arrow-counterclockwise fs-5"></i>
            <span class="fw-semibold d-none d-sm-inline">Reopen (New)</span>
        </button>
    @else
        <button type="button" id="resolverButton"
            onclick="manualTrigger(event, 'ticket-resolver')"
            class="btn d-flex align-items-center gap-2 text-white mark-resolved-btn"
            @if (trim($selectedTicket->status) !== 'In Progress') disabled @endif
            style="height:40px;">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span class="fw-semibold d-none d-sm-inline">Mark as Resolved</span>
        </button>
        <button type="button" id="undoButton" onclick="manualTrigger(event, 'undo-ticket')"
            class="btn d-flex align-items-center gap-2 text-white undo-btn"
            @if (trim($selectedTicket->status) !== 'In Progress') disabled @endif
            style="height:40px;">
            <i class="bi bi-x-circle-fill fs-5"></i>
            <span class="fw-semibold d-none d-sm-inline">Batal Ambil Tiket</span>
        </button>
    @endif
</div>
                    </div>

                    <!-- Row 2: attachment buttons -->
                    <div class="d-flex gap-2 pt-2 flex-wrap">
                        <input type="file" name="attachment[]" id="attachment" class="d-none" multiple disabled>
                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Lampirkan File"
                            @if (!$chatLocked) onclick="document.getElementById('attachment').click();" @endif>
                            <i class="bi bi-paperclip"></i> File
                        </button>

                        <input type="file" name="image[]" id="image" class="d-none"
                            accept=".jpg,.jpeg,.webp,.png" multiple disabled>
                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Lampirkan Gambar"
                            @if (!$chatLocked) onclick="document.getElementById('image').click();" @endif>
                            <i class="bi bi-image"></i> Gambar
                        </button>

                        <button type="button" id="toggleChatHistory" class="btn btn-sm btn-outline-secondary"
                            title="Riwayat Chat">
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
@endsection

@section('scripts')
    @include('partials.fileverifier')
    <!-- Firebase Configuration -->
    <script type="module">
        // Import the functions you need from the SDKs you need
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js";
        import {
            getFirestore,
            collection,
            addDoc,
            onSnapshot,
            query,
            orderBy,
            serverTimestamp
        } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-firestore.js";
        import {
            getAuth,
            signInAnonymously
        } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-auth.js";
        import {
            getAnalytics
        } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-analytics.js";

        // Your web app's Firebase configuration
        const firebaseConfig = {
            apiKey: "AIzaSyCaBfevesjOCPT3WbTgFQq-_GOXFng55qw",
            authDomain: "real-time-chat-b1212.firebaseapp.com",
            projectId: "real-time-chat-b1212",
            storageBucket: "real-time-chat-b1212.firebasestorage.app",
            messagingSenderId: "47924502108",
            appId: "1:47924502108:web:68e83ab67e2ce6285fd099",
            measurementId: "G-P9PR6MQYEP"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);
        const db = getFirestore(app);
        const auth = getAuth(app);

        // Make Firebase available globally
        window.firebaseApp = app;
        window.firebaseDb = db;
        window.firebaseConfig = firebaseConfig;
        window.initRead = "{{ $readChats }}";
        window.firebaseAuth = auth;

        signInAnonymously(auth);
    </script>

    <script>
        //Timezone

        const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        fetch('/set-timezone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                timezone: userTimezone
            })
        });
        // Deklarasi Variabel Global
        let selectedImages = [];
        let selectedAtts = [];
        const otherProf = "{{ $selectedTicket->support_avatar ?? asset('assetsadmin/img/avatars/default.png') }}";
        const userProf = "{{ $selectedTicket->developer_avatar ?? asset('assetsadmin/img/avatars/default.png') }}";
        const allowedImages = ['png', 'jpg', 'jpeg', 'webp'];
        const allowedAtts = ['docx', 'pdf', 'xlsx', 'exe', 'zip', '7zip', 'txt'];
        const maxSize = 100 * 1024 * 1024;
        const maxFiles = 4;

        function getExt(s) {
            return s.split('.').pop().toLowerCase();
        }

        async function allowedIMG(f) {
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

        async function allowedATT(f) {
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
            if (ext == "exe") {
                return true
            }
            if (ext == "zip") {
                return true
            }
            if (ext == "7zip") {
                return true
            }
            if (ext == "txt") {
                return true;
            }

            return false;
        }
    </script>
    <script>
        // Set user data and ticket ID for real-time chat
        window.userData = {
            id: '{{ Auth::id() }}',
            name: '{{ Auth::user()->name }}',
            avatar: '{{ Auth::user()->profile_picture ?? '' }}'
        };

        const routes = {
            client: "{{ url('/files/support') }}",
            support: "{{ url('/files/dev') }}"
        };

        const imgRoutes = {
            client: "{{ url('/images/support') }}",
            support: "{{ url('/images/dev') }}"
        };

        window.ticketId = '{{ $selectedTicket->id }}';
        // Config: set to false to hide avatars and avoid sending avatar URLs to Firestore
        window.chatConfig = {
            showAvatar: false,
            sendAvatar: false
        };
    </script>
    <script src="{{ asset('js/realtime-chat-min.js') }}" defer></script>
    <script type="module" src="{{ asset('js/ticket-list-sort.js') }}"></script>
    <script src="{{ asset('js/chatdev-min.js') }}" defer></script>

    <script>
        window.ticketStatus = "{{ $selectedTicket->status }}";
    </script>
    <script>
        // AJAX Mark as Resolved
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('markResolvedBtn');
            if (!btn) return;
            btn.addEventListener('click', async function() {
                if (btn.disabled) return;
                const url = btn.getAttribute('data-endpoint');
                btn.disabled = true;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }
                    // Option 1: reload to reflect status changes
                    window.location.reload();
                } catch (e) {
                    console.error(e);
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                    alert('Gagal resolve tiket');
                }
            });
        });
    </script>
@endsection
