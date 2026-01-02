@extends('layout.body')
@section('konten')

<div class="container py2 px-0"> {{-- Memberi jarak luar --}}

<link rel="stylesheet" href="{{asset('assetsadmin')}}/vendor/libs/animate-css/animate.css" />
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assetsadmin')}}/vendor/libs/sweetalert2/sweetalert2.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


<script src="{{asset('assetsadmin')}}/vendor/libs/jquery/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="{{asset('assetsadmin')}}/vendor/libs/sweetalert2/sweetalert2.js"></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Sukses',
        text: @json(session('success')),
        timer: 2200,
        timerProgressBar: true,
        showConfirmButton: false
    });
    });
</script>
@endif

<div class="card p-4"> {{-- Tambahkan padding dalam card --}}

    <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-1 mb-1">
        <h2>Data Aplikasi</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">Tambah Aplikasi</button>
    </div>

    <div class="table-responsive text-nowrap">
        <table id="table-app" class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nama Aplikasi</th>
                    <th>Untuk DevRole</th>
                    <th>Dibuat Pada</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($apps as $app)
                <tr>
                    <td>{{ $app->id }}</td>
                    <td>{{ $app->appName }}</td>
                    <td>{{ $app->role->roleName }}</td>
                    <td>{{ $app->created_at ? \Carbon\Carbon::parse($app->created_at)->timezone(session('user_timezone', config('app.timezone')))->format('d-m-Y H:i') : '-' }}</td>
                    <td>
                <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="ti ti-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu">
                    <!-- Tombol Edit -->
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal{{ $app->id }}">
                        <i class="ti ti-pencil me-1"></i> Edit
                    </button>

                    <!-- Tombol Hapus -->
                    <form action="{{ route('app.delete', $app->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus aplikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                        <i class="ti ti-trash me-1"></i> Hapus
                        </button>
                    </form>
                    </div>
                </div>
                </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal{{ $app->id}}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" id="update-form-{{$app->id}}" action="{{ route('app.update', $app->id) }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Aplikasi</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Nama Aplikasi</label>
                                        <input type="text" name="appName" id="update-{{$app->id}}-appName" value="{{ $app->appName }}" oninput="validateInput(this)" class="form-control @error('appName') is-invalid @enderror">
                                        @error('appName')
                                            <div style="color: red; font-size: 13px;">{{ $message }}</div>
                                        @enderror
                                        <span id="err-update-{{$app->id}}-appName" style="color: red; font-size: 13px;"></span>
                                    </div>
                                    <input type="hidden" name="roleID" id="roleID_{{$app->id}}">
                                    <div class="mb-3">
                                        <label for="emailLarge" class="form-label">Role</label>
                                        <select id="update-{{$app->id}}-roleID" class="select2 form-select @error('roleID') is-invalid @enderror">
                                            @foreach ($roles as $r)
                                                <option value="{{ $r->id }}" {{ $app->roleID == $r->id ? 'selected' : '' }}>{{ $r->roleName }}</option>
                                            @endforeach
                                        </select>
                                        @error('roleID')
                                            <span style="color: red; font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                        <span id="err-update-{{$app->id}}-roleID" style="color: red; font-size: 13px;"></span>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" onclick="validateAndUpdate(event, '{{$app->id}}')" id="btn-update-{{$app->id}}" class="btn btn-success">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('app.store') }}" id="form-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Aplikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Aplikasi</label>
                        <input type="text" name="appName" id="appName" class="form-control @error('appName') is-invalid @enderror" oninput="validateInput(this)">
                        @error('appName')
                            <div style="color: red; font-size: 13px;">{{ $message }}</div>
                        @enderror
                        <span id="err-appName" style="color: red; font-size: 13px;"></span>
                    </div>
                    <input type="hidden" name="roleID" id="roleID">
                    <div class="mb-3">
                        <label for="emailLarge" class="form-label">Role</label>
                        <select id="roleID-select" class="select2 form-select @error('roleID') is-invalid @enderror">
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->roleName }}</option>
                            @endforeach
                        </select>
                        @error('roleID')
                            <span style="color: red; font-size: 13px;">{{ $message }}</span>
                        @enderror
                        <span id="err-roleID" style="color: red; font-size: 13px;"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" onclick="validateAndSubmit(event)" id="btn-submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#table-app').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(difilter dari total _MAX_ entri)",
                zeroRecords: "Tidak ada data yang ditemukan",
                paginate: {
                    first: "<<",
                    last: ">>",
                    previous: "<",
                    next: ">"
                },
            }
        });
    });
    function validateInput(inputElement) {
        const inputValue = inputElement.value;
        const forbiddenCharacters = /[^A-Za-z0-9 ]/g; // Karakter yang tidak diinginkan

        if (forbiddenCharacters.test(inputValue)) {
            inputElement.value = inputValue.replace(forbiddenCharacters, ''); // Menghapus karakter yang tidak diinginkan
        }
    }
</script>

</div> {{-- Tutup container --}}

<script>
  const allRoleInputs = document.querySelectorAll('[id^="roleID_"]');
  const mainForm = document.querySelector('#form-form');

  document.addEventListener('DOMContentLoaded', function() {
    const sl = document.getElementById('roleID-select');
    const r = document.getElementById('roleID');

    r.value = sl.value;

    sl.addEventListener('change', function () {
        if (!r.readOnly)
        r.value = this.value;
    });
    
    allRoleInputs.forEach(function(inp) {
      const id = inp.id.replace('roleID_', "")
      const selector = document.getElementById(`update-${id}-roleID`);

      inp.value = selector.value;
      selector.addEventListener('change', function () {
        if (!inp.readOnly)
        inp.value = this.value;
      });
    });
  })

  function reassignUpdateRoleValue(id) {
      const i = document.getElementById('roleID_'+id)
      const selector = document.getElementById(`update-${id}-roleID`);

      i.value = selector.value;
  }
  const fields = ['appName', 'roleID'];
  function clearAjaxErrors() {
    fields.forEach(function(name){
      const input = document.getElementById(name);
      const span = document.getElementById(`err-${name}`);
      if (span) span.textContent = '';
      if (input) {
        input.classList.remove('is-invalid');
        input.readOnly = true;
      }
    });
  }

  function showAjaxErrors(errors) {
    Object.keys(errors).forEach(function(name){
      const input = document.getElementById(name);
      const span = document.getElementById(`err-${name}`);
      if (span) span.textContent = errors[name][0];
      if (input) input.classList.add('is-invalid');
    });
    fields.forEach(function(name){
      const input = document.getElementById(name);
      input.readOnly = false;
    })
    const selector = document.getElementById('roleID-select');
    const role = document.getElementById('roleID');
    //when value is changed, immediately assign it
    role.value = selector.value;
  }

  function clearAjaxErrorsUpdate(id) {
    const fields = ['appName', 'roleID'];
    fields.forEach(function(name){
      const input = document.getElementById(`update-${id}-${name}`);
      const span = document.getElementById(`err-update-${id}-${name}`);
      if (name === "roleID") {
        const r = document.getElementById(`roleID_${id}`)
        r.readOnly = true;
      }
      if (span) span.textContent = '';
      if (input) {
        input.classList.remove('is-invalid');
        input.readOnly = true;
      }
    });
  }

  function showAjaxErrorsUpdate(errors, id) {
    Object.keys(errors).forEach(function(name){
      const input = document.getElementById(`update-${id}-${name}`);
      const span = document.getElementById(`err-update-${id}-${name}`);
      if (span) span.textContent = errors[name][0];
      if (input) {
        input.classList.add('is-invalid');
        input.readOnly = false;
      }
    });
    fields.forEach(function(name){
      const input = document.getElementById(`update-${id}-${name}`);
      input.readOnly = false;
      if (name === "roleID") {
        const r = document.getElementById(`roleID_${id}`)
        r.readOnly = false;
      }
    })
    reassignUpdateRoleValue(id);
  }

  function validateAndSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('form-form');
    const btn  = document.getElementById('btn-submit');
    clearAjaxErrors();

    const fd = new FormData(form);
    fd.append('_token', '{{ csrf_token() }}');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

    $.ajax({
      url: '{{ route("app.validate") }}',
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
          console.error('Terjadi kesalahan saat validasi AJAX', xhr);
        }
      },
      complete: function() {
        btn.innerHTML = 'Tambah';
      }
    });
  }

  function validateAndUpdate(e, id) {
    e.preventDefault();
    const form = document.getElementById(`update-form-${id}`);
    const btn  = document.getElementById(`btn-update-${id}`);
    clearAjaxErrorsUpdate(id);

    const fd = new FormData(form);
    fd.append('_token', '{{ csrf_token() }}');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

    $.ajax({
      url: `/admin/application/update/${id}/validate`,
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
          showAjaxErrorsUpdate(xhr.responseJSON.errors, id);
        } else {
          console.error('Terjadi kesalahan saat validasi AJAX', xhr);
        }
      },
      complete: function() {
        btn.innerHTML = 'Simpan';
      }
    });
  }
</script>

@endsection
