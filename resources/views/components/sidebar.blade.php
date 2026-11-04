<style>
  .sidebar {
    width: 250px;
    height: 100vh;
    background-color: #fff;
    border-right: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
  }

  .nav-link {
    color: #212529 !important;
    padding: 10px 16px;
    border-radius: 8px;
    transition: background-color 0.2s;
  }

  .nav-link.active {
    background-color: #eaf1ff;
    color: #0d6efd !important;
    font-weight: 500;
  }

  .sidebar-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6c757d;
    margin: 12px 0 6px 12px;
    display: block;
  }

  .user-footer {
    padding: 12px 16px;
    border-top: 1px solid #dee2e6;
    background-color: #fff;
    border-radius: 12px;
  }
</style>

<div class="sidebar">
  <!-- Konten utama -->
  <div class="d-flex flex-column flex-grow-1 px-3">
    <!-- Logo -->
    <div class="d-flex align-items-center mb-4 mt-3">
      <img src="{{ asset('assett/div.png') }}" alt="Logo" width="40" height="40" class="me-2" />
      <span class="fw-bold fs-5">TickDesk</span>
    </div>

    <!-- Menu -->
    <label class="sidebar-label">Main Menu</label>
    <a class="nav-link {{ request()->is('Support/chat*') ? 'active' : '' }}" href="{{ route('Chatsup.chatsup') }}">
      <i class="bi bi-chat-dots me-2"></i> Chat
    </a>

    <label class="sidebar-label">Developer</label>
    <a class="nav-link {{ request()->is('*') ? 'active' : '' }}" href="{{ route('Developer.developer') }}">
      <i class="bi bi-journal-text me-2"></i> Task Ticket
    </a>

    <!-- Tombol New Chat -->
    <div class="mt-4">
      <button class="btn btn-primary w-100">+ New Chat</button>
    </div>
  </div>

  <!-- User info -->
  <div class="user-footer d-flex align-items-center gap-2 mx-3 mb-3">
    <img src="{{ $user['avatar'] ?? 'https://i.pravatar.cc/40' }}" class="rounded-circle" width="40" height="40" />
    <div>
      <div class="fw-semibold">{{ $user['name'] ?? 'Guest' }}</div>
      <div class="text-muted text-small">{{ $user['role'] ?? 'User' }}</div>
    </div>
  </div>
</div>
