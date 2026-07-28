@extends('Chatsup.layout.app')

@section('title', 'Tiket Client')

@section('styles')
<style>
  .ticket-scroll {
    overflow-x: hidden;
    height: 100vh;
    overflow-y: auto;
    white-space: nowrap;
    padding-bottom: 8px;
  }

  .ticket-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    background: #fff;
    transition: all 0.3s ease;
  }

  .ticket-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }

  .status-badge {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 600;
  }

  .status-open {
    background-color: #fff3cd;
    color: #856404;
  }

  .status-progress {
    background-color: #cce5ff;
    color: #004085;
  }

  .status-resolved {
    background-color: #d4edda;
    color: #155724;
  }

  .status-closed {
    background-color: #ffcccc;
    color: #571515;
  }

  @media (max-width: 1000px) { 
    .container-fluid {
      margin-left: 16px;
      width: calc(100% - 37px);
    }
  }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Tiket Support</h2>
      </div>

      @if($tickets->isEmpty())
        <div class="text-center py-5">
          <i class="bi bi-inbox display-1 text-muted"></i>
          <h4 class="mt-3 text-muted">Tidak ada tiket</h4>
          <p class="text-muted">Anda belum mempunyai tiket.</p>
        </div>
      @else
      <div class="ticket-scroll">
        <div class="row">
          @foreach($tickets as $ticket)
            @php
              $statusClass = match($ticket->status) {
                  'BELUM DIPROSES' => 'status-open',
                  'SEDANG DIPROSES' => 'status-progress',
                  'SELESAI' => 'status-resolved',
                  'DITUTUP' => 'status-closed',
                  default => 'status-open'
              };

              $statusTxt = match($ticket->status) {
                'BELUM DIPROSES' => 'Open',
                'SEDANG DIPROSES' => 'In Progress',
                'SELESAI' => 'Resolved',
                'DITUTUP' => 'Closed',
                default => 'Open'
              };
            @endphp
            
            <div class="col-md-6 col-lg-4 mb-3" id="tc-{{$ticket->id}}">
              <div class="ticket-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="mb-0 fw-bold">SS-{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y') }}-{{ str_pad($ticket->id, 3, '0', STR_PAD_LEFT) }}</h6>
                  <span class="status-badge {{ $statusClass }}">{{ $statusTxt }}</span>
                </div>
                <h6 class="mb-2">{{ $ticket->title }}</h6>
                <div class="d-flex align-items-center mb-2">
                  <img src="{{ $ticket->client_avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($ticket->client_name) }}" 
                       class="rounded-circle me-2 object-fit-cover" width="24" height="24" alt="Client" />
                  <small class="text-muted">{{ $ticket->client_name }}</small>
                </div>
                @php
                  $timezone = session('user_timezone', config('app.timezone'));
                  $cTime = \Carbon\Carbon::parse($ticket->latest_chat_time ?? $ticket->created_at)->timezone($timezone);
                @endphp
                <small class="text-muted">{{ $cTime }}</small>
                <div class="mt-3" id="button-menu">
                  @if(isset($ticket->supportID) && $ticket->supportID === Auth::id())
                  <a href="{{ route('SupportClientChat.supportclientchat', ['ticket' => $ticket->id]) }}" 
                     class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-chat-dots me-1"></i>Chat
                  </a>
                  @else
                  <button type="submit" id="ticket-taking-button" onclick="duplicationPrevention(event, `ticket-taker-{{$ticket->id}}`)"
                    class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-chat-dots me-1"></i>Ambil Tiket
                  </button>
                  
                  <form method="POST" id="ticket-taker-{{$ticket->id}}" action="{{ route('Support.takeTicket', ['id' => $ticket->id]) }}">
                    @csrf
                  </form>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
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
</script>
@include('Chatsup.partials.supportmenufirebase')
@endsection