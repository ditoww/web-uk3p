<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="icon" type="image/x-icon" href="{{asset('assetsadmin')}}/img/div.png" />
  <title>TicketDesk - @yield('title')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="{{asset('assetsadmin')}}/vendor/libs/jquery/jquery.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
  <style> /* Prevent zooming out entire webpage when max-width is reached */
    html, body {
        height: 100%;
        overflow: hidden;
    }
  </style>
  @stack('styles')
  @yield('styles')
</head>
<body>
  @include('Chatsup.partials.sidebartoggleable')
  <div class="d-flex">
    @include('Chatsup.partials.sidebar')
    <div class="flex-grow-1">
      @yield('content')
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @yield('scripts')
  <script>
  function duplicationPrevention(event, id) { //prevents double submission and submitting on pages you already submitted.
      event.preventDefault();
      const form = document.getElementById(id);
      if (!form.dataset.submitted) {
          form.dataset.submitted = 'true';
          form.requestSubmit();
      }
  }
  function manualTrigger(event, id) { //Reserved for the ticket resolvers.
    event.preventDefault();
    document.getElementById(id).requestSubmit();
  }
  document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('profileBtn');
    var modalEl = document.getElementById('profileModal');
    if (btn && modalEl) {
      btn.addEventListener('click', function() {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
      });
    }
  });
  document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('toggleable-profileBtn');
    var modalEl = document.getElementById('profileModal');
    if (btn && modalEl) {
      btn.addEventListener('click', function() {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
      });
    }
  });
</script>
<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const icon = document.getElementById('sidebar-toggle-icon');
    const navs = document.querySelectorAll('.toggleable-nav-link');
    const isNotShown = !sidebar.classList.contains('show');

    sidebar.classList.add('chuu');

    setTimeout(function () {
      if (isNotShown) {
        sidebar.classList.add('show');
        icon.classList.add('bi-chevron-compact-left');
        icon.classList.remove('bi-chevron-compact-right');
        navs.forEach(nav => nav.classList.add('show'));
      } else {
        sidebar.classList.remove('show');
        icon.classList.add('bi-chevron-compact-right');
        icon.classList.remove('bi-chevron-compact-left');
        navs.forEach(nav => nav.classList.remove('show'));
      }
      sidebar.classList.remove('chuu');
    }, 200); 
  }

  const navs = document.querySelectorAll('.toggleable-nav-link');

  navs.forEach(nav => {
    nav.addEventListener('click', function(e)  {
      if (!nav.classList.contains('show')) e.preventDefault();
    })
  });

  window.addEventListener('resize', function () { //auto close sidebar when resizing
    const sidebar = document.getElementById('sidebar');
    const icon = document.getElementById('sidebar-toggle-icon');
    const navs = document.querySelectorAll('.toggleable-nav-link');
    if (window.innerWidth > 1000 && sidebar.classList.contains('show')) {
      sidebar.classList.remove('show');
      icon.classList.remove('bi-chevron-compact-left');
      icon.classList.add('bi-chevron-compact-right');
      navs.forEach(nav => nav.classList.remove('show'));
    }
  });
  
  document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle-icon');
    const modal = document.getElementById('profileModal');

    if (
      sidebar.classList.contains('show') &&
      !sidebar.contains(e.target) &&
      !modal.contains(e.target) &&
      !toggleBtn.contains(e.target)
    ) {
      sidebar.classList.add('chuu');
      setTimeout(function () {
        sidebar.classList.remove('show');
        toggleBtn.classList.add('bi-chevron-compact-right');
        toggleBtn.classList.remove('bi-chevron-compact-left');

        const navs = document.querySelectorAll('.toggleable-nav-link');
        navs.forEach(function(nav) {
          nav.classList.remove('show');
        });
        sidebar.classList.remove('chuu');
      }, 200);
    }
  });
</script>
</body>
</html>
@include('layout.session')