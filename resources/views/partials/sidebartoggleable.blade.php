<style>
  .toggleable-sidebar {
    width: 280px;
    height: 100vh;
    min-height: 360px;
    background-color: #fff;
    border-right: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: fixed;
    transform: translateX(-90%);
    z-index: 0;
    transition: transform 0.2s ease;
  }

  .center-right-icon {
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    font-size: 28px;
    cursor: pointer;
  }

  .toggleable-sidebar.chuu{
    transform: translateX(-100%);
  }

  .toggleable-sidebar.show{
    transform: translateX(0);
    z-index: 400;
  }
  .toggleable-sidebar.show.chuu{
    transform: translateX(-100%);
  }
  
  .toggleable-nav-link { /*gotten from bootstrap's nav-link*/
    display: block;
    padding: var(--bs-nav-link-padding-y) var(--bs-nav-link-padding-x);
    font-size: var(--bs-nav-link-font-size);
    font-weight: var(--bs-nav-link-font-weight);
    color: var(--bs-nav-link-color);
    text-decoration: none;
    background: 0 0;
    border: 0;
    transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out;
  }
  .toggleable-nav-link {
    color: #212529 !important;
    padding: 10px 16px;
    border-radius: 8px;
  }

  .toggleable-nav-link.active.show {
    background-color: #eaf1ff;
    color: #0d6efd !important;
    font-weight: 500;
  }

  .toggleable-nav-link:hover {
    cursor: default;
  }

  .toggleable-nav-link.show:hover {
    cursor: pointer;
  }

  .toggleable-nav-link.active { /* Makes sure that the background is consistent */
    background-color: #00000000;
    color: #212529 !important;
  }

  .toggleable-user-footer {
    padding: 8px 16px;
    border-top: 1px solid #dee2e6;
    background-color: #fff;
    margin: 9px;
    border-radius: 12px;
    min-width: 200px;
  }

  .toggleable-profile-img {
    width: 40px; /* Fixed width */
    height: 40px; /* Fixed height */
    object-fit: cover; /* Maintain image aspect ratio while filling the container */
  }

    @media (min-width: 1001px) {
    .toggleable-sidebar {
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

<div class="toggleable-sidebar px-3" id="sidebar">
  <div>
    <div class="d-flex align-items-center mb-4 mt-3">
      <img src="{{ asset('assetsadmin/img/div.png') }}" alt="Logo" width="40" height="40" class="me-2" />
      <span class="fw-bold fs-5">TickDesk</span>
    </div>
    <nav class="nav flex-column">
      <a id="nav-link" class="toggleable-nav-link {{ request()->is('dev/taskticket*') ? 'active' : '' }}" href="{{ route('Developer.developer') }}">
        <i class="bi bi-journal-text me-2"></i> Task Ticket
      </a>
      <a id="nav-link" class="toggleable-nav-link {{ request()->is('dev/chatdev*') ? 'active' : '' }}" href="{{ route('Chatdev.chatdev') }}">
        <i class="bi bi-chat-dots me-2"></i> Chat
      </a>
      <a id="nav-link" class="toggleable-nav-link {{ request()->is('dev/chat-history*') ? 'active' : '' }}" href="{{ route('ChatHistory.index') }}">
        <i class="bi bi-clock-history me-2"></i> Chat History
      </a>
      <a id="nav-link" class="toggleable-nav-link {{ request()->is('dev/notes*') ? 'active' : '' }}" href="{{ route('DevNotes.index') }}">
        <i class="bi bi-journal-richtext me-2"></i> Notes
      </a>
    </nav>
  </div>
  <div class="toggleable-user-footer d-flex align-items-center gap-2">
    <button id="toggleable-profileBtn" class="btn btn-light w-100 d-flex align-items-center justify-content-start gap-2 fw-semibold">
      <img src="{{ $user->profile_picture ?? asset('assetsadmin/img/avatars/default.png') }}" class="rounded-circle profile-img" alt="Profile Picture" />
      <span class="mx-auto">
        <span class="shortable mx-auto">{{ $user->name }}</span>
        <span class="shortable text-muted text-small mx-auto">{{ $user->role ?? 'Web Developer' }}</span>
      </span>
    </button>
  </div>
  <i id="sidebar-toggle-icon" class="bi bi-chevron-compact-right center-right-icon" onclick="toggleSidebar();"></i>
</div>
