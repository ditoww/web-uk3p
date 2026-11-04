@extends('layout.app')

@section('title', $ticket->title)

@section('styles')
<style>
  .detail-container {
    max-width: 900px;
    margin: 40px auto;
  }
  .ticket-detail-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    padding: 24px 28px;
    margin-bottom: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
  }
  .ticket-title {
    font-size: 1.35rem;
    font-weight: 700;
    margin-bottom: 0;
  }
  .ticket-status {
    background: #2563eb;
    color: #fff;
    font-size: 13px;
    border-radius: 12px;
    padding: 3px 16px;
    font-weight: 500;
    margin-left: 8px;
    display: inline-block;
  }
  .btn-back {
    background: #f4f6fa;
    color: #222;
    border: none;
    font-weight: 500;
    border-radius: 6px;
    padding: 7px 18px;
    white-space: nowrap;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: background 0.2s;
  }
  .btn-back:hover {
    background: #e2e8f0;
  }
  .btn-take {
    background: #2563eb;
    color: #fff;
    border-radius: 6px;
    font-weight: 500;
    padding: 9px 28px;
    border: none;
    font-size: 1rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    white-space: nowrap;
    transition: background 0.2s;
  }
  .btn-take:hover {
    background: #1746a2;
  }
  .ticket-info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 2px;
  }
  .ticket-id {
    color: #6b7280;
    font-size: 15px;
    font-weight: 500;
  }
  .ticket-meta {
    display: flex;
    gap: 32px;
    margin-top: 10px;
  }
  .ticket-meta-block {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 15px;
  }
  .ticket-meta-label {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
  }
  .ticket-meta-value {
    font-weight: 600;
    color: #222;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 8px;
    border: 1px solid #e5e7eb;
  }
  .problem-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    padding: 22px 28px;
    margin-bottom: 24px;
  }
  .problem-title {
    font-size: 1.07rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #222;
  }
  .problem-desc {
    color: #444;
    font-size: 16px;
    margin-bottom: 0;
    line-height: 1.7;
  }
  @media (max-width: 600px) {
    .detail-container {
      padding: 0 8px;
    }
    .ticket-detail-card, .problem-card {
      padding: 16px 8px;
    }
    .ticket-meta {
      flex-direction: column;
      gap: 8px;
    }
  }
  @media (max-width: 1000px){
    .detail-container {
      margin-left: 32px;
    }
  }
</style>
@endsection

@section('content')
<div class="detail-container px-4">
  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger py-2">{{ session('error') }}</div>
  @endif
  <div class="d-flex justify-content-end align-items-center mb-3">
    <button onclick="window.history.back()" class="btn btn-back">
      &larr; Kembali Ke Menu
    </button>
  </div>
  <div class="ticket-detail-card">
    <div class="ticket-header">
      <div>
        <span class="ticket-title">{{ $ticket->title }}</span>
        @php
          $status = $ticket->status ?? 'Open';
          $normalized = trim(strtolower($status));
          $badgeColor = match($normalized) {
            'Open' => '#2563eb',
            'in progress' => '#f59e0b',
            default => '#6366f1'
          };
          // Normalisasi tampilan kapitalisasi
          $status = $normalized === 'in progress' ? 'In Progress' : ($normalized === 'open' ? 'Open' : $status);
        @endphp
        <span class="ticket-status" style="background: {{ $badgeColor }}">{{ $status }}</span>
      </div>
      @if(($ticket->status ?? 'Open') === 'Open')
        <form method="POST" id="ticket-claim-{{$ticket->id}}" action="{{ route('developer.ticket.claim', $ticket->id) }}">
          @csrf
          <button class="btn btn-take" id="{{$ticket->id}}" onclick="return confirm('Klaim tiket ini?');" type="submit">Ambil Tiket</button>
        </form>
      @endif
    </div>
    <div class="ticket-info-row">
      <span class="ticket-id">Ticket ID: TK-{{ date('Y', strtotime($ticket->created_at)) }}-{{ str_pad($ticket->id, 3, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="ticket-meta mt-2">
      <div class="ticket-meta-block">
        <span class="ticket-meta-label">Dibuat Oleh</span>
        <span class="ticket-meta-value">
          <img 
              src="{{ isset($ticket->user_avatar) && $ticket->user_avatar ? $ticket->user_avatar : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->support_name ?? 'User') }}" 
              class="user-avatar me-1" 
              alt="avatar">
          {{ $ticket->support_name }}
        </span>
      </div>
      <div class="ticket-meta-block">
        <span class="ticket-meta-label">Waktu Dibuat</span>
        <span class="ticket-meta-value">
          {{ \Carbon\Carbon::parse($ticket->created_at)->timezone(session('user_timezone', config('app.timezone')))->format('M d, Y \a\t h:i A') }}
        </span>
      </div>
    </div>
  </div>
  <div class="problem-card">
    <div class="problem-title">Deskripsi Permasalahan</div>
    <p class="problem-desc">{{ $ticket->first_response ?? 'No description available' }}</p>
  </div>
</div>
@endsection
@section('scripts')
<!-- Firebase Configuration -->
<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js";
  import { getFirestore, collection, addDoc, onSnapshot, query, orderBy, serverTimestamp } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-firestore.js";
  import { getAuth, signInAnonymously } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-auth.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-analytics.js";

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
      body: JSON.stringify({ timezone: userTimezone })
  });

  window.userData = {
    id: '{{ Auth::id() }}',
    name: '{{ Auth::user()->name }}',
    email: '{{ Auth::user()->email ?? "<USER TIDAK PUNYA EMAIL>" }}',
    avatar: '{{ Auth::user()->profile_picture ?? "" }}'
  };
</script>
<script src="{{ asset('js/developer-menu-realtime.js') }}"></script>
@endsection