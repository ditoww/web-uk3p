@extends('layout.body')
@section('konten')

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

<div class="">
    <div class="card">
        <div class="card-header">
            <h4>Edit Data Akun Admin</h4>
        </div>
        <div class="card-body">
            <form method="POST" id="form-form" action="/admin/admin/update-data/{{$akun->username}}" enctype="multipart/form-data">
                @csrf

                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ $akun->username }}" readonly>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" value="{{ $akun->password ?? '' }}">
                    @error('password')
                        <span style="color: red; font-size: 13px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Nama -->
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" name="nama" oninput="validateInput(this)" class="form-control @error('nama') is-invalid @enderror" value="{{ $akun->name }}">
                    <span id="error-message" style="color: red;"></span>
                    @error('nama')
                        <span style="color: red; font-size: 13px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $akun->email ?? '' }}">
                    @error('email')
                        <span style="color: red; font-size: 13px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- No HP -->
                <div class="mb-3">
                    <label for="nohp" class="form-label">No HP</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text @error('nohp') invalid-style @enderror">+62</span>
                        <input type="number" name="nohp" class="form-control @error('nohp') is-invalid @enderror" value="{{ $akun->phone_number ?? '' }}">
                    </div>
                    @error('nohp')
                        <span style="color: red; font-size: 13px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Foto Profil -->
                <div class="mb-3">
                    <label for="upload" class="form-label">Foto Profil</label><br>
                    <img id="previewImg" src="{{ $akun->profile_picture ?? asset('assetsadmin/img/avatars/default.png') }}" class="img-thumbnail w-px-100 h-px-100 rounded-circle mb-2" style="object-fit: cover;">
                    <input type="file" name="upload" id="file-input" class="form-control @error('upload') is-invalid @enderror" accept=".png,.jpeg,.jpg">
                    @error('upload')
                        <span style="color: red; font-size: 13px;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Tombol -->
                <button type="submit" onclick="duplicationPrevention(event,'form-form')" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.admin') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@include('partials.fileverifier')
<script>
    let lastValidFile = null;
    let invoked = false;
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
    const fileInput = document.getElementById('file-input');
    async function handleFile(element, file) {
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

        const allowed = await allowedIMG(file);
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
    const preview = document.getElementById('previewImg');
    fileInput.addEventListener('change', async function (e) {
        const file = e.target.files && e.target.files[0];
        await handleFile(fileInput, file);
        if (lastValidFile) {
            preview.src = window.URL.createObjectURL(lastValidFile);
        }
        invoked = true; 
    });

    fileInput.addEventListener('drop', async function (e) {
        e.preventDefault();
        const file = e.dataTransfer.files && e.dataTransfer.files[0];
        await handleFile(fileInput, file);
        if (lastValidFile) {
            preview.src = window.URL.createObjectURL(lastValidFile);
        }
        invoked = true;
    });
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
    function duplicationPrevention(event, id) { //prevents double submission and submitting on pages you already submitted.
        event.preventDefault();
        const form = document.getElementById(id);
        if (!form.dataset.submitted) {
            form.dataset.submitted = 'true';
            form.requestSubmit();
        }
    }
    function previewImage(input) {
        const preview = document.getElementById('previewImg');
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
        }
    }
    document.querySelectorAll('input[type=number]').forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            const badKeys = ['e', 'E', '+', '-']; //We don't have any exponential values, so this blocks it.
            if (badKeys.includes(e.key)) {
                e.preventDefault();
            }
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
            }
        });

        input.addEventListener('wheel', function(e) {
            e.preventDefault();
        });
    });
</script>

@endsection
