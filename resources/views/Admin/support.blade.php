@extends('layout.body')
@section('konten')

<link rel="stylesheet" href="{{asset('assetsadmin')}}/vendor/libs/animate-css/animate.css" />
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assetsadmin')}}/vendor/libs/sweetalert2/sweetalert2.css" />

                  <!-- build:js assets/vendor/js/core.js -->
     <script src="{{asset('assetsadmin')}}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{asset('assetsadmin')}}/vendor/libs/popper/popper.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="{{asset('assetsadmin')}}/js/ui-modals.js"></script>
    <!-- <script src="{{asset('assetsadmin')}}/js/pages-account-settings-account.js"></script> -->
    <script src="{{asset('assetsadmin')}}/vendor/libs/sweetalert2/sweetalert2.js"></script>

      <!-- Notifikasi hasil aksi (berbasis flash session) -->
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
  @if (session('error'))
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: @json(session('error')),
          timer: 2500,
          timerProgressBar: true,
          showConfirmButton: false
        });
      });
    </script>
  @endif

<script>
    function showAlerte() {
        Swal.fire({
            title: 'Fitur Segera Hadir!',
            text: 'Bersabar Yah...',
            icon: 'error',
            showConfirmButton: false,
            timer: 2500
        });
    }
    </script>

<style>

    .ssedtt{
    cursor:pointer;
    }
    
    </style>
<style>
    .input-group:focus-within .input-group-text.invalid-style{
      border-color: rgb(234, 84, 85);
    }

    .invalid-style {
      border-color: rgb(234, 84, 85);
    }
    /* Disables input number UI */
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }
</style>

<div class="card">
    <div class="card-header">
<div class=" d-flex flex-column mb-3 flex-md-row justify-content-between align-items-center"> <!-- Menambahkan class align-items-center -->
<h2>Data Akun Support</h2>
<div >


    <div class="btn btn-label-primary dropdown-toggle me-2" data-bs-toggle="dropdown" ><i class="ti ti-file-export me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span></div>
    <button type="button" data-bs-toggle="modal" class="btn btn-primary" data-bs-target="#tambahModal"><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Tambah Data</span></button>

    <div class="dropdown-menu">
     {{-- <a class="dropdown-item" href="javascript:void(0);" id="printTable"
     ><i class="ti ti-copy me-1" ></i>Copy</a> --}}
     <a class="dropdown-item ssedtt" href="javascript:void(0);" id="csvTable"
     ><i class="ti ti-file-spreadsheet me-1" ></i>Exel</a>
     <a class="dropdown-item ssedtvt" href="javascript:void(0);" id="excelTable"
      ><i class="ti ti-file-text me-1"></i>CSV</a>
      <a class="dropdown-item ssdelee" href="javascript:void(0);" id="pdfTable"
      ><i class="ti ti-file-description me-1"></i>Pdf</a>
      <a class="dropdown-item ssdelee" href="javascript:void(0);"  id="copyTable"
      ><i class="ti ti-printer me-1" ></i>Print</a>
    </div>
</div>
</div>


<div class="table-responsive text-nowrap mb-2">
<table id="table-user" class="table table-hove display">
<thead class="table-light">
  <tr>
    <th>ID</th>
    <th>Username</th>
    <th>Nama</th>
    <th>Status</th>
    <th>Dibuat Pada</th>
    <th>Aksi</th>
  </tr>
</thead>
<tbody class="table-border-bottom-0 mb-5">
  @foreach ($alldata as $p)
    <tr>
      <th scope="row">{{$loop->iteration}}</th>
      <td class="p-3">{{$p->username}}</td>
      <td>{{$p->name}}</td>
      <td>
        {{--
        @if($p->status === 'active')
          <span class="badge rounded-pill" style="background-color: #d2f4e8; color: #1f7f5c; padding: 6px 12px; font-weight: 600;">Active</span>
        @elseif($p->status === 'Away')
          <span class="badge rounded-pill" style="background-color: #fef3c7; color: #92400e; padding: 6px 12px; font-weight: 600;">Away</span>
        @else
          <span class="badge rounded-pill" style="background-color: #e0e0e0; color: #333; padding: 6px 12px; font-weight: 600;">{{$p->status}}</span>
        @endif
        --}}

        @if(Cache::has('user-online-' . $p->id))
          <span class="badge rounded-pill" style="background-color: #d2f4e8; color: #1f7f5c; padding: 6px 12px; font-weight: 600;">Active</span>
        @else
          <span class="badge rounded-pill" style="background-color: #fef3c7; color: #92400e; padding: 6px 12px; font-weight: 600;">Away</span>
        @endif
      </td>
      <td>{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->timezone(session('user_timezone', config('app.timezone')))->format('d-m-Y H:i') : '-' }}</td>
        <!-- <img src="gambar" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;"> -->
      
      <td>
        @if(Auth::user()->isSuperAdmin())
        <div class="dropdown">
          <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
            <i class="ti ti-dots-vertical"></i>
          </button>
          <div class="dropdown-menu">
          <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#largeModal{{$p->username}}"
              ><i class="ti ti-list-details me-1"></i></i> Detail Data</button>
            <a class="dropdown-item ssedtt" href="/admin/support/edit-data/{{$p->username}}"
           
              ><i class="ti ti-pencil me-1"></i> Edit Data</a>
              <a class="dropdown-item ssdele" href="javascript:void(0);"
            data-user="{{$p->username}}"
            data-nama="{{$p->name}}">
            <i class="ti ti-trash me-1"></i> Hapus Data
            </a>
          </div>
        </div>
        @elseif(Auth::user()->isAdmin())
        <button type="button" class="bg-transparent border-0" data-bs-toggle="modal" data-bs-target="#userModal-{{ $loop->index }}"
        ><i class="ti ti-list-details me-1"></i></button>
        @endif
      </td>
    
    </tr>
@endforeach
</tbody>

</table >
</div>
</div>


                    
        <!--  Modal Tambah-->
                    <div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <form id="form-form" method="POST" action="/admin/support/tambah-data" enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="modal-header">
                              <h3 class="modal-title fw-bold" id="exampleModalLabel3">Tambah Akun</h3>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <hr class="my-0 mb-4" />
                                <div class="d-flex align-items-start align-items-sm-center mb-3 gap-4">
                                  <img src="{{ asset('assetsadmin/img/avatars/default.png') }}" alt="user-avatar" class="d-block w-px-100 h-px-100 rounded-circle" style="object-fit: cover;" id="uploadedAvatar" />
                                  <div class="button-wrapper">
                                    <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
                                      <span class="d-none d-sm-block">Upload Foto Baru</span>
                                      <i class="ti ti-upload d-block d-sm-none"></i>
                                      <input type="file" id="upload" name="upload" class="account-file-input" data-stopper="not active" hidden accept=".png,.jpeg,.jpg" />
                                    </label>
                                    <button type="button" class="btn btn-label-secondary account-image-reset mb-3">
                                      <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                                      <span class="d-none d-sm-block">Reset</span>
                                    </button>
                                    <div class="text-muted">Diperbolehkan bentuk JPG atau PNG. Maksimal 5MB</div>
                                    @error('upload')
                                      <div id="byp-upload" style="color: red; font-size: 13px;">{{ $message }}</div>
                                    @enderror
                                    <div id="error-upload" style="color: red; font-size: 13px;"></div>
                                  </div>
                                </div>

                                <!-- Username -->
                                <div class="col mb-3">
                                  <label for="usernameField" class="form-label">Username</label>
                                  <input type="text" id="usernameField" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="SupOne" oninput="validateUserInput(this)" />
                                  <span id="error-message-user" style="color: red; font-size: 13px;"></span>
                                  @error('username')
                                    <div id="byp-username" style="color: red; font-size: 13px;">{{ $message }}</div>
                                  @enderror
                                  <div id="error-username" style="color: red; font-size: 13px;"></div>
                                </div>
                              </div>

                              <!-- Nama -->
                              <div class="row g-2 mb-3">
                                <div class="col mb-0">
                                  <label for="inputHuruf" class="form-label">Nama</label>
                                  <input type="text" id="inputHuruf" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Support One" oninput="validateInput(this)" />
                                  <span id="error-message" style="color: red; font-size: 13px;"></span>
                                   @error('nama')
                                    <div id="byp-nama" style="color: red; font-size: 13px;">{{ $message }}</div>
                                  @enderror
                                  <div id="error-nama" style="color: red; font-size: 13px;"></div>
                                </div>
                              </div>

                              <!-- Email & No HP -->
                              <div class="row g-2 mb-3">
                                <div class="col mb-0">
                                  <label for="emailInput" class="form-label">Email (Opsional)</label>
                                  <input type="text" id="emailInput" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="example@mail.com" />
                                  @error('email')
                                    <div id="byp-email" style="color: red; font-size: 13px;">{{ $message }}</div>
                                  @enderror
                                  <div id="error-email" style="color: red; font-size: 13px;"></div>
                                </div>
                                <div class="col mb-0">
                                  <label for="phoneNumber" class="form-label">No HP (Opsional)</label>
                                  <div class="input-group input-group-merge">
                                    <span class="input-group-text @error('nohp') is-invalid @enderror">+62</span>
                                    <input type="number" id="phoneNumber" name="nohp" class="form-control @error('nohp') is-invalid @enderror" placeholder="81234567890" />
                                  </div>
                                  @error('nohp')
                                    <div id="byp-nohp" style="color: red; font-size: 13px;">{{ $message }}</div>
                                  @enderror
                                  <div id="error-nohp" style="color: red; font-size: 13px;"></div>
                                </div>
                              </div>

                              <!-- Hidden datetime -->
                              <input type="datetime-local" id="tgl" hidden name="tgl" />

                              <!-- Password -->
                              <div class="row g-2 mb-3">
                                <div class="col mb-0">
                                  <div class="form-password-toggle">
                                    <label class="form-label" for="basic-default-password12">Password</label>
                                    <div class="input-group">
                                      <input type="password" name="pass" class="form-control @error('pass') is-invalid @enderror" id="basic-default-password12" placeholder="••••••••••••" autocomplete="new-password" />
                                      <span id="basic-default-password2" class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                    @error('pass')
                                      <div id="byp-pass" style="color: red; font-size: 13px;">{{ $message }}</div>
                                    @enderror
                                    <div id="error-pass" style="color: red; font-size: 13px;"></div>
                                  </div>
                                </div>
                              </div>

                            </div>

                            <!-- Footer -->
                            <div class="modal-footer">
                              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                              <button type="button" id="btn-submit" onclick="validateAndSubmitSupport(event)" name="submit" class="btn btn-primary me-2">Tambah Data</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  <!--  Modal Tambah-->

                  <script>
                    // Fungsi untuk mengatur nilai elemen input datetime-local menjadi tanggal dan waktu saat ini
                    function setDateTime() {
                        var now = new Date(); // Mendapatkan tanggal dan waktu saat ini
                        var year = now.getFullYear();
                        var month = (now.getMonth() + 1).toString().padStart(2, '0'); // Bulan dimulai dari 0
                        var day = now.getDate().toString().padStart(2, '0');
                        var hours = now.getHours().toString().padStart(2, '0');
                        var minutes = now.getMinutes().toString().padStart(2, '0');
                        var dateTimeString = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
                        document.getElementById('tgl').value = dateTimeString; // Mengatur nilai elemen input
                    }
            
                    // Panggil fungsi setDateTime saat halaman dimuat
                    setDateTime();
                </script>




                  <!-- Detail Modal -->
                  @foreach ($alldata as $p)
                  <div class="modal fade" id="largeModal{{$p->username}}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h3 class="modal-title" id="exampleModalLabel3">Detail Akun</h3>
                          <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                        </div>
                        <br>
                        <hr class="my-0" />
                        <div class="modal-body">
                        <div class="d-flex align-items-start align-items-sm-center mb-3 gap-4">
                    <img
                      src="{{$p->profile_picture ?? asset('assetsadmin/img/avatars/default.png')}}"
                      alt="user-avatar"
                      class="d-block w-px-100 h-px-100 rounded-circle" style="object-fit: cover;"
                      id="uploadedAvatar" />

                  </div>
                          <div class="row">
                            <div class="col mb-3">
                              <label for="nameLarge" class="form-label">Username</label>
                              <input type="text" readonly class="form-control" placeholder="" value="{{$p->username}}" />
                            </div>
                          </div>
                          <div class="row g-2 mb-3">
                            <div class="col mb-0">
                              <label for="dobLarge" class="form-label">Nama</label>
                              <input type="text" readonly class="form-control" placeholder="" value="{{$p->name}}" />
                            </div>
                            <div class="col mb-0">
                              <label for="emailLarge" class="form-label">Status</label>
                              <input type="text" value="{{$p->status}}" readonly class="form-control" placeholder="" />
                            </div>
                          </div>
                          <div class="row g-2 mb-3">
                            <div class="col mb-0">
                              <label for="emailField" class="form-label">Email</label>
                              <input type="text" id="emailField" readonly class="form-control" placeholder="" value="{{$p->email}}" />
                            </div>
                            <div class="col mb-0">
                              <label for="phoneField" class="form-label">No HP</label>
                              <input type="text" id="phoneField" value="{{$p->phone_number}}" readonly class="form-control" placeholder="" />
                            </div>
                          </div>
                           
                          <div class="row g-2 mb-3">
                            <div class="col mb-0">
                              <label for="createdAtField" class="form-label">Dibuat Pada</label>
                              <input type="text" id="createdAtField" class="form-control" value="{{$p->created_at ?? '-'}}" readonly placeholder="" />
                            </div>
                          </div>
                            
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Close
                          </button>
                          <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                        </div>
                      </div>
                    </div>
                  </div>
                  @endforeach
               


<style>
.ssdele:hover{
background-color:#DE3163;
color:#eaeaea;
}

.ssdelee:hover{
background-color:#DE3163;
color:#eaeaea;
}

.ssedtt:hover{
background-color:#53B956;
color:#eaeaea;
}
.ssedtvt:hover{
background-color:#EAE041;
color: #fff;;
}
#table-controls {
margin-bottom: 10px;
}

/* Menyembunyikan tombol-tombol JS bawaan DataTables */
.dt-buttons {
display: none;
z-index: 100;
}

div.dataTables_length {
float: left;
}
div.dataTables_filter {
float: right;
}


div.dataTables_info {
float: left;
}
div.dataTables_paginate {
float: right;
}

</style>

@include('partials.fileverifier')
<script>

function duplicationPrevention(event, id) { //prevents double submission and submitting on pages you already submitted.
  event.preventDefault();
  const form = document.getElementById(id);
  if (!form.dataset.submitted) {
      form.dataset.submitted = 'true';
      form.requestSubmit();
  }
}

function validateInput(inputElement) {
  const inputValue = inputElement.value;
  const forbiddenCharacters = /[^A-Za-z ]/g; // Karakter yang tidak diinginkan

  if (forbiddenCharacters.test(inputValue)) {
    document.getElementById('error-message').textContent = 'Tidak boleh mengandung karakter tertentu, seperti @, angka, atau karakter lainnya.';
    inputElement.value = inputValue.replace(forbiddenCharacters, ''); // Menghapus karakter yang tidak diinginkan
  } else {
    document.getElementById('error-message').textContent = '';
  }
}

function validateUserInput(inputElement) {
  const inputValue = inputElement.value;
  const forbiddenCharacters = /[^A-Za-z0-9]/g; // Karakter yang tidak diinginkan

  if (forbiddenCharacters.test(inputValue)) {
    document.getElementById('error-message-user').textContent = 'Tidak boleh mengandung karakter tertentu, seperti @, spasi, atau karakter lainnya.';
    inputElement.value = inputValue.replace(forbiddenCharacters, ''); // Menghapus karakter yang tidak diinginkan
  } else {
    document.getElementById('error-message-user').textContent = '';
  }
}

let lastValidFile = null;
const fileInput = document.querySelector('.account-file-input');
const allowedImages = ['png', 'jpg', 'jpeg'];
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

  return false;
}

async function handleFile(element, file) {
  if (!file) {
    if (lastValidFile) {
      const dt = new DataTransfer();
      dt.items.add(lastValidFile);
      element.files = dt.files;
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
      } else {
        element.value = "";
      }
      return;
    }

  const allowed = await allowedIMG(file);
  if (!allowed) {
    if (lastValidFile) {
      const dt = new DataTransfer();
      dt.items.add(lastValidFile);
      element.files = dt.files;
    } else {
      element.value = "";
    }
    return;
  }

  if (fileInput.dataset.stopper === "not active") {
    lastValidFile = file;

    const dt = new DataTransfer();
    dt.items.add(file);
    element.files = dt.files;

    return;
  }
  else if (fileInput.dataset.stopper === "active") {
    if (lastValidFile) {
        const dt = new DataTransfer();
        dt.items.add(lastValidFile);
        element.files = dt.files;
    } else {
        element.value = "";
    }
    return;
  }
}

document.addEventListener('DOMContentLoaded', function () {
(function () {
// Update/reset user image on the account page
const accountUserImage = document.getElementById('uploadedAvatar');
const fileInput = document.querySelector('.account-file-input');
const resetFileInput = document.querySelector('.account-image-reset');

if (accountUserImage) {
  const resetImage = accountUserImage.src;

  /*fileInput.onchange = () => {
    if (fileInput.files[0]) {
      accountUserImage.src = window.URL.createObjectURL(fileInput.files[0]);
    }
  };*/

  fileInput.addEventListener('change', async function (e) {
    e.preventDefault();
    const file = e.target.files && e.target.files[0];
    await handleFile(fileInput, file);
    if (lastValidFile) {
      accountUserImage.src = window.URL.createObjectURL(lastValidFile);
    }
  });

  resetFileInput.onclick = () => {
    e.preventDefault();
    fileInput.value = '';
    lastValidFile = null;
    accountUserImage.src = resetImage;
  };
}
})();
});

</script>



<script>
$(document).ready(function() {
  $('.ssdele').click(function() {
      var user = $(this).data('user');
      var nama = $(this).data('nama');

      Swal.fire({
  title: 'Apakah Anda yakin ingin menghapus data nama ' + user + '?',
  text: "Tindakan ini tidak dapat dibatalkan!",
  icon: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  confirmButtonText: 'Ya, Hapus Data!',
  cancelButtonText: 'Tidak',
  showClass: {
      popup: 'animate__animated animate__tada'
  },
  customClass: {
      confirmButton: 'btn btn-primary me-3',
      cancelButton: 'btn btn-danger'
  },
  buttonsStyling: false
}).then((result) => {
          if (result.isConfirmed) {
              $.ajax({
                  type: 'DELETE', // Ubah method menjadi DELETE
                  url: '/admin/support/user/hapus-akun/' + user,
                  data: {
                      _token: '{{ csrf_token() }}'
                  },
                  success: function(response) {
                    if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2500
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1800
                        }).then(() => {
                            window.location.href = "{{ route('akun.support') }}";
                        });
                    }
                },
                  error: function(xhr, status, error) {
                      Swal.fire({
                          icon: 'error',
                          title: 'Oops...',
                          text: xhr.responseText
                      });
                  }
              });
          }
      });
  });
});
</script>



<script>
$(document).ready(function() {
// Inisialisasi DataTables
var table = $('#table-user').DataTable({
    "language": {
        "search": "Cari:",
        "lengthMenu": "Tampilkan _MENU_ entri",
        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
        "infoFiltered": "(difilter dari total _MAX_ entri)",
        "zeroRecords": "Tidak ada data yang ditemukan",
        "paginate": {
            "first": "<<",
            "last": ">>",
            "previous": "<",
            "next": ">"
        }
    },
    "format": {
        body: function (inner, coldex, rowdex) {
            if (!inner) return inner;
            var el = $.parseHTML(inner);
            var result = '';

            el.forEach(function (item) {
                if (item.classList !== undefined && item.classList.contains('user-name')) {
                    result += item.textContent;
                } else {
                    result += item.innerText || item.textContent;
                }
            });

            return result;
        },
    },
    "lengthMenu": [10, 25, 50],
    dom: '<"top"Blfr>t<"bottom"ip>',
});


// Hapus tombol-tombol JS yang ingin Anda sembunyikan
$('.dt-button').remove();

// Tambahkan fungsi klik untuk tombol dropdown menu ke tombol DataTables yang sudah ada
$("#printTable").on('click', function() {
    table.button('0').trigger();
});
$("#csvTable").on('click', function() {
    table.button('1').trigger();
});
$("#excelTable").on('click', function() {
    table.button('2').trigger();
});
$("#pdfTable").on('click', function() {
    table.button('3').trigger();
});
$("#copyTable").on('click', function() {
    table.button('4').trigger();
});
});
</script>

                          {{-- <div class="col mb-0">
                              
                          <label class="form-label" for="phoneNumber">No HP</label>
                        <div class="input-group input-group-merge">
                          <span class="input-group-text">ID (+62)</span>
                          <input
                            type="number"
                             required
                            name="nohp"
                            class="form-control"
                            placeholder="" />
                        </div>
                          
                            </div> --}}


<script>
  const fields = ['username','nama','email','nohp','role','pass','upload'];
  function clearAjaxErrorsSupport() {
    fileInput.dataset.stopper = "active";
    const map = {
      username: { input: '[name="username"]', holder: '#error-username' },
      nama:     { input: '[name="nama"]',     holder: '#error-nama' },
      email:    { input: '[name="email"]',    holder: '#error-email' },
      nohp:     { input: '[name="nohp"]',     holder: '#error-nohp' },
      pass:     { input: '[name="pass"]',     holder: '#error-pass' },
      upload:   { input: '[name="upload"]',   holder: '#error-upload' },
    };
    document.getElementById('error-message').textContent = '';
    document.getElementById('error-message-user').textContent = '';
    Object.values(map).forEach(({input, holder}) => {
      const el = document.querySelector(input);
      const h  = document.querySelector(holder);
      const byp = document.getElementById(`byp-${name}`);
      if (h) h.textContent = '';
      if (byp) byp.textContent = '';
      if (el) {
        el.classList.remove('is-invalid');
        el.readOnly = true;
      }
    });
    // hilangkan style invalid untuk prefix +62
    const addon = document.querySelector('[name="nohp"]')?.closest('.input-group')?.querySelector('.input-group-text');
    if (addon) addon.classList.remove('invalid-style');
  }

  function showAjaxErrorsSupport(errors) {
    fileInput.dataset.stopper = "not active";
    const map = {
      username: { input: '[name="username"]', holder: '#error-username' },
      nama:     { input: '[name="nama"]',     holder: '#error-nama' },
      email:    { input: '[name="email"]',    holder: '#error-email' },
      nohp:     { input: '[name="nohp"]',     holder: '#error-nohp' },
      pass:     { input: '[name="pass"]',     holder: '#error-pass' },
      upload:   { input: '[name="upload"]',   holder: '#error-upload' },
    };
    Object.keys(errors).forEach((k) => {
      const cfg = map[k];
      if (!cfg) return;
      const el = document.querySelector(cfg.input);
      const h  = document.querySelector(cfg.holder);
      if (h) h.textContent = errors[k][0];
      if (el) el.classList.add('is-invalid');
      if (k === 'nohp') {
        const addon = el.closest('.input-group')?.querySelector('.input-group-text');
        if (addon) addon.classList.add('invalid-style');
      }
    });
    fields.forEach(function(name){
      const input = document.querySelector(`[name="${name}"]`);
      if (input) 
        input.readOnly = false;
    });
  }

  function validateAndSubmitSupport(e) {
    e.preventDefault();
    const form = document.getElementById('form-form');
    const btn  = document.getElementById('btn-submit');
    clearAjaxErrorsSupport();

    const fd = new FormData(form);
    fd.append('_token', '{{ csrf_token() }}');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

    $.ajax({
      url: '{{ route("admin.support.validate") }}',
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
          showAjaxErrorsSupport(xhr.responseJSON.errors);
        } else {
          console.error('Validasi AJAX gagal:', xhr);
        }
      },
      complete: function() {
        btn.innerHTML = 'Tambah Data';
      }
    });
  }
</script>

@endsection