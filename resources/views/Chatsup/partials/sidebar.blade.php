<style>
  .sidebar {
    width: 280px;
    min-width: 280px;
    height: 100vh;
    min-height: 360px;
    background-color: #fff;
    border-right: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .nav-link {
    color: #212529 !important;
    padding: 10px 16px;
    border-radius: 8px;
  }

  .nav-link.active {
    background-color: #eaf1ff;
    color: #0d6efd !important;
    font-weight: 500;
  }

  .user-footer {
    padding: 8px 16px;
    border-top: 1px solid #dee2e6;
    background-color: #fff;
    margin: 9px;
    border-radius: 12px;
    min-width: 200px;
  }

  .profile-img {
    width: 40px; /* Fixed width */
    height: 40px; /* Fixed height */
    object-fit: cover; /* Maintain image aspect ratio while filling the container */
  }

  @media (max-width: 1000px) {
    .sidebar {
      display: none;
    }
  }

  .shortable{
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
  }
</style>

<div class="sidebar px-3">
  <div>
    <div class="d-flex align-items-center mb-4 mt-3">
      <img src="{{ asset('assetsadmin/img/div.png') }}" alt="Logo" width="40" height="40" class="me-2" />
      <span class="fw-bold fs-5">TickDesk</span>
    </div>
    <nav class="nav flex-column">
      <a class="nav-link {{ request()->is('support/ticket*') ? 'active' : '' }}" href="{{ route('Support.support') }}">
        <i class="bi bi-journal-text me-2"></i> Tiket Client
      </a>
      <a class="nav-link {{ request()->is('support/clientchat*') ? 'active' : '' }}" href="{{ route('SupportClientChat.supportclientchat') }}">
        <i class="bi bi-chat-dots me-2"></i> Chat Client
      </a>
      <a class="nav-link {{ request()->is('support/devtickets/create') ? 'active' : '' }}" href="{{ request()->is('support/clientchat*') ? route('SupportClientChat.createDevTicketPage', ['ticket_user' => request('ticket')]) : route('SupportClientChat.createDevTicketPage') }}">
        <i class="bi bi-journal-plus me-2"></i> Buat Dev Ticket
      </a>
      <a class="nav-link {{ request()->is('Support/chat*') && !request()->boolean('history') ? 'active' : '' }}" href="{{ route('Chatsup.chatsup') }}">
        <i class="bi bi-chat-dots me-2"></i> Chat Developer
      </a>
      <a class="nav-link {{ request()->is('Support/chat*') && request()->boolean('history') ? 'active' : '' }}" href="{{ route('Chatsup.chatsup', ['history' => 1]) }}">
        <i class="bi bi-clock-history me-2"></i> Chat History
      </a>
    </nav>
  </div>
  <div class="user-footer d-flex align-items-center gap-2">
    <button id="profileBtn" class="btn btn-light w-100 d-flex align-items-center justify-content-start gap-2 fw-semibold">
      <img src="{{ $user->profile_picture ?? asset('assetsadmin/img/avatars/default.png') }}" class="rounded-circle profile-img" alt="Profile Picture" />
      <span class="mx-auto">
        <span class="shortable mx-auto">{{ $user->name }}</span>
        <span class="shortable text-muted text-small mx-auto">{{ $user->role ?? 'Support' }}</span>
      </span>
    </button>
  </div>
</div>

<!-- Modal Popup Profile -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="{{ $user->profile_picture ?? asset('assetsadmin/img/avatars/default.png') }}" class="rounded-circle mb-2 object-fit-cover" width="60" height="60" alt="Profile Picture" />
        <div class="fw-semibold fs-5">{{ $user->name }}</div>
        <div class="text-muted mb-3">{{ $user->role ?? 'Support' }}</div>
        <!-- Tambahkan menu lain di sini jika perlu -->
        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" onclick="duplicationPrevention(event, 'logoutForm')" data-bs-dismiss="modal" class="btn btn-outline-danger w-100 mt-2">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</div>