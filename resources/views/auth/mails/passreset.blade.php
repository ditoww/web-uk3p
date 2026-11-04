{{--@extends('layout.mail')
@section('header-title', 'Reset Password')
@section('form-title', 'Reset Password')

@section('form-content')
    @php
        $resetUrl = config('app.url') . '/password/reset/' . $token . '?email=' . urlencode($email);
    @endphp

    <h4>Selamat datang ke Seventhsoft Ticketing, {{$name}}</h4>

    <p>Terdapat sebuah permintaan untuk reset password Anda. Mohon klik tombol tersebut untuk melanjutkan penggantian password.</p>
    <br />
    <p>Token tersebut akan hangus setelah 5 menit.</p>
    <br />
    <a href="{{ $resetUrl }}" style="color: #fff; background-color: #0b234a; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        Reset Password
    </a>
    <br />
    <p>Jika Anda tidak bisa mengklik tombol di atas, klik link dibawah ini.</p>
    <br />
    <p><a href="{{ $resetUrl }}" style="word-break: break-word; hyphens: none; font-weight: 700; color: #0b234a;">{{ $resetUrl }}</a></p>
    <br />
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
@endsection

@section('foot-info')
    <img src="{{asset('storage/email-logo.png')}}" style="width: 7.5vw; min-width: 64px;" alt="logo"/>
@endsection--}}

@php
    $resetUrl = config('app.url') . '/password/reset/' . $token . '?email=' . urlencode($email);
@endphp

<div style="background-color:#F6F9FF;color:#171717;margin:0;padding:0;font-family:Inter,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;">
    <tr>
      <td align="center" valign="top" style="padding:32px 0;">

        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="576" style="background:#fff;border-radius:8px;padding:32px;box-sizing:border-box;text-align:left;">
          <tr>
            <td style="text-align:center;">
              <h2 style="margin:0 0 24px 0;font-weight:600;font-size:32px;text-align:center;">Reset Password</h2>
            </td>
          </tr>
          <tr>
            <td>
              <h4>Halo, <b>{{ $name }}.</b></h4>
              <p>Kami mendapat sebuah permintaan untuk reset password Anda. Mohon klik tombol tersebut untuk melanjutkan perubahan password.</p>
              <p>Link tersebut akan hangus setelah 5 menit.</p>
              <a href="{{ $resetUrl }}" style="color: #fff; background-color: #0b234a; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Reset Password
              </a>
              <p>Jika Anda tidak bisa mengklik tombol di atas, klik link dibawah ini.</p>
              <p><a href="{{ $resetUrl }}" style="word-break: break-word; hyphens: none; font-weight: 700; color: #0b234a;">{{ $resetUrl }}</a></p>
              <p>Apabila jika Anda tidak meminta reset password, Anda dapat mengabaikan email ini.</p>
            </td>
          </tr>
        </table>

        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;">
          <tr>
            <td align="center">
              <img src="{{ $message->embed(public_path('email-logo.png')) }}"
                   alt="Seventhsoft Logo"
                   style="display:block;width:80px;height:auto;margin:auto;">
            </td>
          </tr>
        </table>

      </td>
    </tr>
  </table>
</div>