@extends('layout.mail')
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
@endsection