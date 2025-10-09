@extends('Chatsup.layout.app')

@section('title', 'Buat Dev Ticket')

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Buat Dev Ticket</h4>
    <div class="d-flex gap-2">
      @if(request('ticket'))
        <a href="{{ route('SupportClientChat.supportclientchat', ['ticket' => request('ticket')]) }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Chat
        </a>
      @else
        <a href="{{ route('SupportClientChat.supportclientchat') }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Chat
        </a>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card position-relative">
    <div class="card-body" style="z-index: 2;">
      <form method="POST" action="{{ route('SupportClientChat.createDevTicket') }}" enctype="multipart/form-data" id="create-dev-ticket-form">
        @csrf

        <!-- Hidden: akan diisi dari picker -->
        <input type="hidden" name="client_ticket_id" id="client_ticket_id" value="{{ old('client_ticket_id', $selectedId ?? '') }}"/>

        <!-- Sumber Komplain -->
        <div class="mb-3">
          <label class="form-label">Sumber Komplain <span class="text-danger">*</span></label>
          <div class="d-flex flex-wrap gap-4">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="case_type" id="case_type_ticket" value="ticket" {{ old('case_type','ticket') === 'ticket' ? 'checked' : '' }}>
              <label class="form-check-label" for="case_type_ticket">Terkait Ticket Client</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="case_type" id="case_type_external" value="external" {{ old('case_type') === 'external' ? 'checked' : '' }}>
              <label class="form-check-label" for="case_type_external">Komplain di luar sistem (telepon, dsb.)</label>
            </div>
          </div>
        </div>

        <!-- Ticket Client + tombol di bawah label -->
        <div id="ticketSection">
          <div class="mb-2">
            <label class="form-label mb-1">Ticket Client <span class="text-danger">*</span></label>
            <button type="button" id="openTicketPickerBtn" class="btn btn-sm btn-outline-primary">
              Pilih Ticket
            </button>
          </div>
          <div class="mb-3">
            <div id="selectedTicketBadge" class="badge bg-light text-dark text-wrap" style="white-space: normal; font-size: 14px;">
              @php
                $selId = old('client_ticket_id', $selectedId ?? null);
                $sel = isset($tickets) ? collect($tickets)->firstWhere('id', (int) $selId) : null;
              @endphp
              {{ $selId ? ('#'.$selId.' - '.($sel->title ?? '')) : 'Belum dipilih' }}
            </div>
          </div>
        </div>

        <!-- Role (untuk komplain di luar sistem) -->
        <div class="mb-3" id="roleSection" style="display:none;">
          <label class="form-label">Pilih Role <span class="text-danger">*</span></label>
          <select name="role_id" id="role_id" class="form-select">
            <option value="">-- Pilih Role --</option>
            @foreach(($roles ?? []) as $role)
              <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                {{ $role->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Judul <span class="text-danger">*</span></label>
          <input
            type="text"
            name="title"
            class="form-control"
            maxlength="255"
            required
            placeholder="Judul Ticket"
            value="{{ old('title') }}"
            oninput="validateTitleInput(this);"
          />
        </div>

        <div class="mb-2 position-relative">
          <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
          <textarea
            name="description"
            id="description"
            class="form-control"
            rows="8"
            required
            maxlength="10000"
            placeholder="Deskripsi issue untuk developer..."
          >{{ old('description') }}</textarea>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3">
          <small id="description-word-count" class="text-muted">0 / 10000</small>
          <small id="description-error" class="text-danger" style="display:none;">Deskripsi tersebut terlalu panjang</small>
        </div>

        <div class="mb-3">
          <label class="form-label">Attachment Gambar (opsional)</label>
          <input type="file" id="devTicketImageInput" name="image" accept=".jpg,.jpeg,.png,.webp" class="form-control"/>
          <small class="text-muted">Max 2MB. JPG/PNG/WEBP.</small>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle me-1"></i> Buat
          </button>
          <a
            href="{{ request('ticket') ? route('SupportClientChat.supportclientchat', ['ticket' => request('ticket')]) : route('SupportClientChat.supportclientchat') }}"
            class="btn btn-light"
          >
            Batal
          </a>
        </div>
      </form>
    </div>

    <!-- Panel Picker Ticket -->
    <div id="ticketPicker" class="shadow dropdown-menu show py-0"
         style="display:none; position: fixed; max-height: 320px; overflow:auto; z-index: 1055; min-width: 300px;">
      <div class="px-3 py-2 border-bottom bg-light sticky-top" style="border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem;"> <!--Matching the one-->
        <input type="text" class="form-control form-control-sm" placeholder="Cari ticket..." id="ticketSearch">
      </div>
      <div id="ticketList">
        @foreach(($tickets ?? []) as $t)
          <button type="button"
                  class="dropdown-item d-flex align-items-start gap-2 py-2 ticket-item"
                  data-id="{{ $t->id }}"
                  data-title="{{ $t->title ?? '' }}">
            <span class="text-muted">#{{ $t->id }}</span>
            <span class="flex-grow-1 text-truncate">{{ $t->title ?? 'Tanpa judul' }}</span>
          </button>
        @endforeach
        @if(empty($tickets) || count($tickets) === 0)
          <div class="px-3 py-2 text-muted">Tidak ada ticket tersedia.</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
@include('partials.fileverifier')
<script>
(function() {
  var devTicketDescription = document.getElementById('description');
  var devTicketImageInput = document.getElementById('devTicketImageInput');
  var wordCountDisplay = document.getElementById('description-word-count');
  var descriptionError = document.getElementById('description-error');
  var clientTicketInput = document.getElementById('client_ticket_id');
  var selectedTicketBadge = document.getElementById('selectedTicketBadge');
  var ticketPicker = document.getElementById('ticketPicker');
  var ticketSearch = document.getElementById('ticketSearch');
  var openPickerBtn = document.getElementById('openTicketPickerBtn');
  var ticketSection = document.getElementById('ticketSection');
  var roleSection = document.getElementById('roleSection');
  var roleSelect = document.getElementById('role_id');
  var form = document.getElementById('create-dev-ticket-form');
  var charLimit = 10000;

  var currentAnchor = null; // anchor aktif untuk positioning

  function validateTitleInput(el) {
    el.value = el.value.replace(/[\x00-\x1F\x7F]/g, '');
  }
  window.validateTitleInput = validateTitleInput;

  function updateDescriptionCounter() {
    var count = devTicketDescription.value.length;
    wordCountDisplay.textContent = count + ' / ' + charLimit;
    if (count > charLimit) {
      devTicketDescription.classList.add('is-invalid');
      descriptionError.style.display = 'inline';
    } else {
      devTicketDescription.classList.remove('is-invalid');
      descriptionError.style.display = 'none';
    }
  }

  // Toggle UI berdasar sumber komplain
  function getCaseType() {
    var checked = document.querySelector('input[name="case_type"]:checked');
    return checked ? checked.value : 'ticket';
  }
  function updateCaseTypeUI() {
    var type = getCaseType();
    if (type === 'ticket') {
      ticketSection.style.display = '';
      roleSection.style.display = 'none';
      if (roleSelect) roleSelect.required = false;
    } else {
      ticketSection.style.display = 'none';
      roleSection.style.display = '';
      if (roleSelect) roleSelect.required = true;
      // pastikan dropdown ticket tertutup
      closeTicketPicker();
    }
  }

  // Lebarkan dropdown: min 420px, clamp ke lebar card-body
  function positionPicker(anchor) {
    if (!anchor || ticketPicker.style.display !== 'block') return;

    var arect = anchor.getBoundingClientRect();
    var container = document.querySelector('.card-body') || document.body;
    var crect = container.getBoundingClientRect();

    var margin = 12;
    var minW = 420;
    var maxW = Math.max(320, crect.width - margin * 2);
    var desiredWidth = Math.min(maxW, Math.max(arect.width, minW));

    var left = Math.min(
      Math.max(arect.left, crect.left + margin),
      crect.right - margin - desiredWidth
    );

    var top = arect.bottom + 4;

    ticketPicker.style.width = desiredWidth + 'px';
    ticketPicker.style.left = left + 'px';
    ticketPicker.style.top = top + 'px';
  }

  function openTicketPicker(anchor) {
    if (getCaseType() !== 'ticket') return; // hanya saat mode ticket
    currentAnchor = anchor || openPickerBtn;
    ticketPicker.style.display = 'block';
    positionPicker(currentAnchor);
    if (ticketSearch) {
      ticketSearch.value = '';
      filterTickets('');
      setTimeout(function(){ ticketSearch.focus(); }, 0);
    }
  }

  function closeTicketPicker() {
    ticketPicker.style.display = 'none';
    currentAnchor = null;
  }

  function filterTickets(q) {
    var items = ticketPicker.querySelectorAll('.ticket-item');
    var query = String(q || '').toLowerCase();
    items.forEach(function(btn) {
      var id = String(btn.dataset.id || '').toLowerCase();
      var title = String(btn.dataset.title || '').toLowerCase();
      btn.style.display = (id.indexOf(query) !== -1 || title.indexOf(query) !== -1) ? '' : 'none';
    });
  }

  function setSelectedTicket(id, title) {
    clientTicketInput.value = id;
    selectedTicketBadge.textContent = '#' + id + ' - ' + (title || '');
  }

  // Events
  if (devTicketDescription) {
    devTicketDescription.addEventListener('input', updateDescriptionCounter);
  }
  if (openPickerBtn) {
    openPickerBtn.addEventListener('click', function(){ openTicketPicker(openPickerBtn); });
  }

  window.addEventListener('resize', function() {
    if (ticketPicker.style.display === 'block') positionPicker(currentAnchor);
  });
  window.addEventListener('scroll', function() {
    if (ticketPicker.style.display === 'block') positionPicker(currentAnchor);
  }, true);

  if (ticketSearch) {
    ticketSearch.addEventListener('input', function(e){ filterTickets(e.target.value); });
  }
  if (ticketPicker) {
    ticketPicker.addEventListener('click', function(e) {
      var btn = e.target.closest('.ticket-item');
      if (!btn) return;
      var id = btn.dataset.id;
      var title = btn.dataset.title || '';
      setSelectedTicket(id, title);
      closeTicketPicker();
    });
  }

  document.addEventListener('click', function(e) {
    if (ticketPicker.style.display !== 'block') return;
    if (!ticketPicker.contains(e.target) && e.target !== openPickerBtn) {
      closeTicketPicker();
    }
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeTicketPicker();
  });

  document.addEventListener('DOMContentLoaded', function() {
    updateDescriptionCounter();
    updateCaseTypeUI();
  });

  var caseTypeRadios = document.querySelectorAll('input[name="case_type"]');
  caseTypeRadios.forEach(function(r){
    r.addEventListener('change', updateCaseTypeUI);
  });

  async function validateImageFile(input) {
    var file = input.files && input.files[0];
    if (!file) return;

    var max2MB = 2 * 1024 * 1024;
    if (file.size > max2MB) {
      input.value = '';
      return;
    }

    if (typeof allowedIMG === 'function') {
      var ok = await allowedIMG(file);
      if (!ok) {
        input.value = '';
      }
    } else {
      var ok2 = ['image/jpeg','image/png','image/jpg','image/webp'].indexOf(file.type) !== -1;
      if (!ok2) {
        input.value = '';
      }
    }
  }

  if (devTicketImageInput) {
    devTicketImageInput.addEventListener('change', function(){ validateImageFile(devTicketImageInput); });
  }

  if (form) {
    form.addEventListener('submit', function(e) {
      var type = getCaseType();
      if (type === 'ticket') {
        if (!clientTicketInput.value) {
          e.preventDefault();
          alert('Pilih Ticket Client terlebih dahulu.');
          openTicketPicker(openPickerBtn);
          return;
        }
      } else {
        if (!roleSelect || !roleSelect.value) {
          e.preventDefault();
          alert('Pilih Role terlebih dahulu untuk komplain di luar sistem.');
          if (roleSelect) roleSelect.focus();
          return;
        }
        // bersihkan client_ticket_id agar tidak terkirim
        clientTicketInput.value = '';
      }
    });
  }
})();
</script>
@endsection