@extends('Chatsup.layout.app')

@section('title', 'Buat Dev Ticket')

@section('styles')
  <style>
    .scroll {
      overflow-x: hidden;
      max-height: 100vh;
      overflow-y: auto;
      white-space: nowrap;
    }
  </style>
@endsection

@section('content')
<div class="container scroll py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Buat Dev Ticket</h4>
    <div class="d-flex gap-2">
      @if(request('ticket_user'))
        <a href="{{ route('SupportClientChat.supportclientchat', ['ticket' => request('ticket_user')]) }}" class="btn btn-outline-secondary">
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
    <div id="byp" class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <div id="err" class="alert alert-danger d-none">
    <ul class="mb-0">
    </ul>
  </div>
  <div class="card position-relative">
    <div class="card-body" style="z-index: 2;">
      <form method="POST" action="{{ route('SupportClientChat.createDevTicket') }}" enctype="multipart/form-data" id="create-dev-ticket-form">
        @csrf

        <!-- Hidden: akan diisi dari picker -->
        <input type="hidden" name="client_ticket_id" id="client_ticket_id" value="{{ old('client_ticket_id', $selectedId ?? '') }}" />

        <!-- Sumber Komplain -->
        <div class="mb-3">
          <label class="form-label">Sumber Komplain <span class="text-danger">*</span></label>
          <div class="d-flex flex-wrap gap-4">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="case_type" id="case_type_ticket" value="ticket" {{ old('case_type') === 'ticket' ? 'checked' : '' }}>
              <label class="form-check-label" for="case_type_ticket">Terkait Ticket Client</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="case_type" id="case_type_external" value="external" {{ old('case_type','external') === 'external' ? 'checked' : '' }}>
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
          <input type="hidden" id="role_id" name="role_id">
          <select id="role_id_select" class="form-select">
            <option value="">-- Pilih Role --</option>
            @foreach(($roles ?? []) as $role)
              <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                {{ $role->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Judul Tiket <span class="text-danger">*</span></label>
          <input
            type="text"
            name="title"
            id="title"
            class="form-control"
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
            maxlength="10000"
            placeholder="Deskripsi issue untuk developer..."
          >{{ old('description') }}</textarea>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3">
          <small id="description-word-count" class="text-muted">0 / 10000</small>
          <small id="description-error" class="text-danger" style="display:none;">Deskripsi tersebut terlalu panjang</small>
        </div>

        <div class="mb-3">
          <label class="form-label">Attachment (opsional)</label>
          <div class="d-flex align-items-center">
            <input type="file" id="devTicketImageInput" name="image" accept=".jpg,.jpeg,.png,.webp,.xlsx,.docx,.pdf" class="form-control w-75"/>          
            <div class="mx-4">
              <button type="button" class="btn btn-sm btn-outline-danger" id="removeAttachmentBtn">
                <i class="bi bi-x-circle me-1"></i> Hapus Attachment
              </button>
            </div>
          </div>
          <small class="text-muted">Max 5MB. JPG / PNG / WEBP / PDF / DOCX / XLSX (gambar akan dikompres otomatis).</small>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary" onclick="validateAndSubmit(event)" id="btn-submit">
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
  window.currentAnchor = null;
  document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('role_id_select');
    const role = document.getElementById('role_id');

    // init value
    role.value = selector.value;

    selector.addEventListener('change', function () {
        if (!role.readOnly)
        role.value = this.value;
    });
});
(function() {
  const allowedImages = ['png', 'jpg', 'jpeg', 'webp'];
  const allowedAtts = ['docx', 'pdf','xlsx'];
  const maxSize = 5 * 1024 * 1024;

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
  const btn  = document.getElementById('btn-submit');
  var charLimit = 10000;

  //window.currentAnchor = null; // anchor aktif untuk positioning

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
      if (roleSelect) clientTicketInput.disabled = false;
    } else {
      ticketSection.style.display = 'none';
      roleSection.style.display = '';
      if (roleSelect) clientTicketInput.disabled = true;
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
    if (getCaseType() !== 'ticket' || btn.disabled) return; // hanya saat mode ticket
    window.currentAnchor = anchor || openPickerBtn;
    ticketPicker.style.display = 'block';
    positionPicker(window.currentAnchor);
    if (ticketSearch) {
      ticketSearch.value = '';
      filterTickets('');
      setTimeout(function(){ ticketSearch.focus(); }, 0);
    }
  }

  function closeTicketPicker() {
    ticketPicker.style.display = 'none';
    window.currentAnchor = null;
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
    if (ticketPicker.style.display === 'block') positionPicker(window.currentAnchor);
  });
  window.addEventListener('scroll', function() {
    if (ticketPicker.style.display === 'block') positionPicker(window.currentAnchor);
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

  let lastValidFile = null;
  let invoked = false;

  async function validateFile(element, file) {
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

  if (devTicketImageInput) {
    devTicketImageInput.addEventListener('change', async function (e) {
      const file = e.target.files && e.target.files[0];
      await validateFile(devTicketImageInput, file);
      invoked = true;
    });

    devTicketImageInput.addEventListener('drop', async function (e) {
      e.preventDefault();
      const file = e.dataTransfer.files && e.dataTransfer.files[0];
      await validateFile(devTicketImageInput, file);
      invoked = true;
    });
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

  const removeAttachmentBtn = document.getElementById('removeAttachmentBtn');

  if (removeAttachmentBtn && devTicketImageInput) {
    removeAttachmentBtn.addEventListener('click', function () {
      lastValidFile = null;
      invoked = false;

      devTicketImageInput.value = '';

      devTicketImageInput.dispatchEvent(
        new Event('change', { bubbles: true })
      );
    });
  }
})();
</script>
<script>
  const fileInput = document.getElementById('devTicketImageInput');
  const clientTicketInput = document.getElementById('client_ticket_id');

  const fields = ['title','description'];

  function getCaseType() {
    var checked = document.querySelector('input[name="case_type"]:checked');
    return checked ? checked.value : 'external';
  }

  function clearAjaxErrors() {
    fileInput.readOnly = true;
    const byp = document.getElementById('byp');
    const err = document.querySelector('#err');
    if (byp) byp.textContent = '';
    if (err) {
      err.innerHTML = "";
      err.classList.toggle('d-none', true)
    }
    if (getCaseType() === 'ticket') {
      var ticketPicker = document.getElementById('ticketPicker');
      ticketPicker.style.display = 'none';
      window.currentAnchor = null;
    } else {
      const role = document.getElementById('role_id');
      role.readOnly = true;
    }
    fields.forEach(function(name) {
        const input = document.getElementById(name);
        input.readOnly = true;
      }
    )
  }

  function showAjaxErrors(errors) {
    fileInput.readOnly = false;
    const err = document.querySelector('#err');
    let ers = [];
    Object.keys(errors).forEach(function(name){
      const eria = errors[name][0];
      ers.push(`<li>${eria}</li>`)
    });
    const allium = ers.join('\n');
    if (err) {
      err.classList.toggle('d-none', false);
      err.innerHTML = `<ul class="mb-0">${allium}</ul>`;
    }
    if (getCaseType() !== 'ticket') {
      const selector = document.getElementById('role_id_select');
      const role = document.getElementById('role_id');
      role.value = selector.value;
      role.readOnly = false;
    }
    fields.forEach(function(name) {
        const input = document.getElementById(name);
        if (input) input.readOnly = false;
      }
    )
  }

  function validateAndSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('create-dev-ticket-form');
    const btn  = document.getElementById('btn-submit');
    clearAjaxErrors();

    const fd = new FormData(form);
    fd.append('_token', '{{ csrf_token() }}');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Buat';

    $.ajax({
      url: '{{ route("SupportClientChat.validateDevTicket") }}',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      success: function() {
        if (typeof form.requestSubmit === 'function') {
          form.requestSubmit();
        } else {
          HTMLFormElement.prototype.submit.call(form);
        }
      },
      error: function(xhr) {
        btn.disabled = false;
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
          showAjaxErrors(xhr.responseJSON.errors);
        } else {
          console.error('Validasi AJAX gagal:', xhr);
        }
      },
      complete: function() {
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Buat';
      }
    });
  }
</script>
@endsection