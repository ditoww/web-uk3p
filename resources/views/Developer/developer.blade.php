@extends('layout.app')

@section('title', 'Tiket Developer')

@section('styles')
<style>
  .main-content {
    padding: 30px;
  }

  .ticket-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    padding: 16px;
    margin-bottom: 16px;
    border: 1px solid #e0e0e0;
  }

  .ticket-type {
    font-size: 12px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 12px;
    color: white; /* Ensures text is white */
  }

  .ticket-type.web { background-color: #5B9BFF; }
  .ticket-type.mobile { background-color: #7DD3A8; }
  .ticket-type.desktop { background-color: #C084FC; }

  .status-badge {
    border: 1px solid #0d6efd;
    color: #0d6efd;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 14px;
    background-color: #fff;
  }

  .btn-take {
    background-color: #28a745;
    color: white;
  }

  .btn-open {
    background-color: #0d6efd;
    color: white;
  }

  .btn-take:hover {
    background-color: #218838;
  }

  .tabs .nav-link {
    color: #000;
    border: none;
    font-weight: 500;
  }

  .tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 3px solid #0d6efd;
  }

  #ticket-list {
    max-height: 70vh;
    overflow-y: auto;
    padding-right: 10px;
  }

  .linebox {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    display: flex;
    align-items: center;
    flex-shrink: 0;
  }

  .ticket-meta {
    display: flex;
    flex-wrap: nowrap;
    flex-direction: row;
    font-size: 14px;
  }

  .ticket-meta > .linebox + .linebox {
    margin-left: 1rem;
  }

  @media (max-width: 400px) {
    .ticket-meta {
      flex-direction: column;
      align-items: flex-start;
    }

    .ticket-meta > .linebox + .linebox {
      margin-left: 0;
      margin-top: 2px;
    }

    .ticket-meta i.bi-clock {
      margin-left: .35rem !important;
      margin-right: .5rem !important;
    }
  }

  .user-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
  }

  .carder {
    flex-direction: row;
  }
  @media (max-width: 1000px){
    .main-content {
      margin-left: 32px;
    }
  }

  @media (max-width: 600px) {
    .carder {
      flex-direction: column;
    }
  }
</style>
@endsection

@section('content')
<div class="main-content">
  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger py-2">{{ session('error') }}</div>
  @endif
  <h4 class="fw-bold text-nowrap">Tiket Developer</h4>

  <ul class="nav nav-tabs tabs mb-3" id="role-tabs">
    <li class="nav-item"><a class="nav-link" data-role="all" href="#">Semua Tiket</a></li>
    @foreach ($roles as $role)
    <li class="nav-item"><a class="nav-link {{Auth::user()->devRoleID === $role->id ? 'active' : ''}}" data-role="{{$role->id}}" href="#">{{$role->roleName}}</a></li>
    @endforeach
  </ul>

  <div id="ticket-list">
    @if($tickets->isEmpty())
      <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h4 class="mt-3 text-muted">Tidak ada tiket</h4>
        <p class="text-muted">Anda belum mempunyai tiket.</p>
      </div>
    @else
      <div id="ticket-cards">
        @php
          $sortedTickets = ($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator || $tickets instanceof \Illuminate\Pagination\Paginator)
            ? $tickets->getCollection()
            : $tickets;
        @endphp
        @foreach ($sortedTickets as $ticket)
        <div id="ticket-number-{{$ticket->id}}" class="ticket-card" data-role="{{$ticket->roleID}}">
          <div class="d-flex justify-content-between carder">
            <div>
              <div class="small text-muted linebox">
                {{ "TK-" . \Carbon\Carbon::parse($ticket->created_at)->format('Y') . "-" . str_pad($ticket->id, 3, '0', STR_PAD_LEFT) }}
                <span class="ticket-type 
                  @if(strtolower($ticket->roleName) == 'web developer') web
                  @elseif(strtolower($ticket->roleName) == 'mobile developer') mobile
                  @elseif(strtolower($ticket->roleName) == 'desktop developer') desktop
                  @endif
                  ms-2">
                  {{ $ticket->roleName }}
                </span>
              </div>
              <h6 class="mt-2 mb-1">{{ $ticket->title }}</h6>
              <div class="text-muted ticket-meta" style="font-size: 14px;">
                <div class="linebox">
                  <img 
                    src="{{ isset($ticket->user_avatar) && $ticket->user_avatar ? $ticket->user_avatar : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->support_name ?? 'User') }}" 
                    class="user-avatar me-1" 
                    alt="avatar">
                  {{ $ticket->support_name }}
                </div> 
                <div class="linebox">
                  @php
                    $timezone = session('user_timezone', config('app.timezone'));
                    $cTime = \Carbon\Carbon::parse($ticket->created_at)->timezone($timezone);
                  @endphp
                  <i class="bi bi-clock ms-3 me-1"></i> {{ $cTime->format('d M Y H:i') }}
                </div>
              </div>
            </div>
            <div class="text-end">
              <div class="ticket-actions mt-2">
                <button class="btn btn-sm btn-open" onclick="window.location.href='{{ route('developer.ticket.detail', $ticket->id) }}'">Open</button>
                <form method="POST" id="ticket-claim-{{$ticket->id}}" action="{{ route('developer.ticket.claim', $ticket->id) }}" style="display:inline;">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-take" id="{{$ticket->id}}" onclick="return confirm('Klaim tiket ini?');">Ambil Tiket</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>

      <!-- Pesan kosong untuk kondisi filter, disembunyikan default -->
      <div id="empty-filter" class="text-center py-5 d-none">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h4 class="mt-3 text-muted">Tidak ada tiket</h4>
        <p class="text-muted">Anda belum mempunyai tiket.</p>
      </div>
    @endif
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
  }).catch(() => {}); // hindari error di console bila route belum ada

  document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll("#role-tabs .nav-link");
    const cards = document.querySelectorAll(".ticket-card");
    const emptyFilter = document.getElementById('empty-filter');

    function applyFilter(role) {
      if (!cards.length) return;
      let visible = 0;
      cards.forEach(card => {
        const cardRole = card.getAttribute("data-role");
        const show = (role === "all" || cardRole === role);
        card.style.display = show ? "block" : "none";
        if (show) visible++;
      });
      if (emptyFilter) {
        emptyFilter.classList.toggle('d-none', visible > 0);
      }
    }

    tabs.forEach(tab => {
      tab.addEventListener("click", function (e) {
        e.preventDefault();
        tabs.forEach(t => t.classList.remove("active"));
        this.classList.add("active");
        const role = this.getAttribute("data-role");
        applyFilter(role);
      });
    });

    const initialTab = document.querySelector("#role-tabs .nav-link.active") || document.querySelector('#role-tabs .nav-link[data-role="all"]');
    if (initialTab) {
      applyFilter(initialTab.getAttribute("data-role"));
    }

    // Tambah: cegah double submit pada klaim
    document.querySelectorAll('form[id^="ticket-claim-"]').forEach(form => {
      form.addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
          btn.disabled = true;
          btn.textContent = 'Mengklaim...';
        }
      });
    });
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
