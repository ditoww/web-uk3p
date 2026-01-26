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

@if (session('success'))
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
        <h2>Request Penggantian Password</h2>
    </div>

    <div class="table-responsive text-nowrap">
        <table id="table-app" class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama Asli</th>
                    <th>Role</th>
                    <th>Dibuat Pada</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($alldata as $app)
                <tr>
                    <td>{{ $app->id }}</td>
                    <td>{{ $app->username }}</td>
                    <td>{{ $app->name }}</td>
                    <td>{{ $app->privLevel }}</td>
                    <td>{{ \Carbon\Carbon::parse($app->created_at)->timezone(session('user_timezone', config('app.timezone')))->format('d-m-Y H:i') }}</td>
                    <td>
                    <button type="button" class="bg-transparent border-0 text-danger ssdele" data-bs-toggle="modal" data-bs-target="#editModal{{ $app->id }}">
                        <i class="ti ti-pencil me-1"></i>
                    </button>
                </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal{{ $app->id}}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('noemailPass.update', $app->id) }}" id="update-form-{{$app->id}}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Username</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Nama Password</label>
                                        <input type="password" name="pass" id="update-{{$app->id}}-pass" oninput="validateInput(this)" class="form-control @error('pass') is-invalid @enderror">
                                        @error('pass')
                                            <div style="color: red; font-size: 13px;">{{ $message }}</div>
                                        @enderror
                                        <span id="err-update-{{$app->id}}-pass" style="color: red; font-size: 13px;"></span>
                                    </div>
                                    <div class="mb-3">
                                        <label>Konfirmasi Password</label>
                                        <input type="password" name="pass_confirmation" id="update-{{$app->id}}-pass_confirmation" oninput="validateInput(this)" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="btn-update-{{$app->id}}" onclick="validateAndUpdate(e, '{{$app->id}}')" class="btn btn-success">Ganti Password</button>
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
        const forbiddenCharacters = /[^\x20-\x7E]/g;; // Karakter yang tidak diinginkan

        if (forbiddenCharacters.test(inputValue)) {
            inputElement.value = inputValue.replace(forbiddenCharacters, ''); // Menghapus karakter yang tidak diinginkan
        }
    }
</script>

<script>
  function clearAjaxErrorsUpdate(id) {
    const fields = ['pass','pass_confirmation'];
    fields.forEach(function(name){
      const input = document.getElementById(`update-${id}-${name}`);
      const span = document.getElementById(`err-update-${id}-${name}`);
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
      url: `/admin/password/${id}/validate`,
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
        btn.innerHTML = 'Ganti Password';
      }
    });
  }
</script>

</div> {{-- Tutup container --}}

@endsection
