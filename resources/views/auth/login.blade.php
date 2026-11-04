<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" type="image/x-icon" href="{{asset('assetsadmin')}}/img/div.png" />
  <title>TickDesk Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f9fbfd;
      font-family: 'Segoe UI', sans-serif;
    }

    .login-card {
      max-width: 400px;
      width: 100%;
      padding: 40px 30px;
      background-color: white;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .login-logo {
      display: block;
      margin: 0 auto 15px auto;
      width: 50px;
      height: 50px;
    }

    .form-control {
      border-radius: 10px;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .btn-login {
      border-radius: 10px;
      background-color: #146CFF;
      color: white;
      transition: background-color 0.3s ease;
    }

    .btn-login:hover {
      background-color: #125ee3;
    }

    .form-text a {
      font-weight: 500;
      color: #146CFF;
      text-decoration: none;
    }

    .form-text a:hover {
      text-decoration: underline;
    }

    footer {
      text-align: center;
      font-size: 0.8rem;
      color: #adb5bd;
      margin-top: 40px;
    }
  </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100 px-3">

  <div class="login-card text-center">
    <!-- Ganti URL logo di bawah jika perlu -->
    <img src="{{asset('assetsadmin')}}/img/email-logo.png" alt="TickDesk Logo" class="login-logo" />
    <h5 class="fw-bold">TickDesk</h5>
    <p class="mt-2 mb-4 text-muted"><small>Selamat Datang! Mohon isi kredensial Anda.</small></p>
    @if (session('status'))
      <div style="color: green; font-size: 14px; margin-bottom: 16px;">
        {{session('status')}}
      </div>
    @endif
    <form id="form" method="POST" action="{{ route('loginPost') }}">
      @csrf
      <div class="mb-3 text-start">
        <label for="login" class="form-label fw-semibold">Email atau Username</label>
        <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" id="login" placeholder="Masukkan email atau username">
        @error('login')
          <span style="color: red; font-size: 13px;">{{ $message }}</span>
        @enderror
      </div>
      <div class="mb-3 text-start">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Enter your password">
        @error('password')
          <span style="color: red; font-size: 13px;">{{ $message }}</span>
        @enderror
      </div>
      <div class="mb-3 text-end">
        <a href="/forgot-password" class="form-text">Lupa Password?</a>
      </div>
      <div class="d-grid">
        <button type="submit" onclick="duplicationPrevention(event, 'form')" class="btn btn-login">Login</button>
      </div>
    </form>
  </div>
<script>
function duplicationPrevention(event, id) { //prevents double submission and submitting on pages you already submitted.
  event.preventDefault();
  const form = document.getElementById(id);
  if (!form.dataset.submitted) {
      form.dataset.submitted = 'true';
      form.requestSubmit();
  }
}
</script>
<script>
    //Timezone

  const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

  fetch('/set-timezone', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      body: JSON.stringify({ timezone: userTimezone })
  });
</script>
</body>
</html>
