<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Seventhsoft Ticketing</title>
    <link rel="icon" href="{{asset('assetsadmin/img/logo.png')}}">

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite('resources/js/app.js')

    <style>
        body {
            margin: 0;
            background-color: #F6F9FFFF;
            font-family: 'Inter', 'Arial', sans-serif;
        }
        
        .form-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px;
            min-height: calc(100vh - 64px);
            box-sizing: border-box;
            font-weight: 400;
        }

        .form-title {
            font-size: 32px;
            font-weight: 400;
            margin: 32px 0;
            color: #171717;
        }

        .form-box {
            background-color: #ffffff;
            color: #171717;
            padding: 32px;
            border-radius: 8px;
            width: 90vw;
            max-width: 676px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .button-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .submit-button {
            padding: 12px;
            background-color: #0b234a;
            width: 35%;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-button:hover {
            background-color: #8B0404;
            text-decoration: underline;
            text-decoration-thickness: 2px;
        }

        .submit-button:active {
            background-color: #660707;
        }

        @media (max-width: 650px) { /* When tablet is in portrait mode*/
            .submit-button {
                width: 55%;
            }
        }

        @media (max-width: 500px) { /* When phone is in portrait mode*/
            .submit-button {
                width: 80%;
            }
        }

        @media (max-width: 373px) { /* When phone is in portrait mode*/
            .submit-button {
                width: 90%;
            }
        }

        .foot-info {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
        }

    </style>
    @yield('style')
</head>
<body>
    <div id="app">
        <main>
            <div class="form-wrapper">
                <div class="form-title">
                    @yield('form-title')
                </div>

                <div class="form-box" style="max-width: 576px; width: 100%; padding: 32px; margin: 0 auto; box-sizing: border-box;">
                    @yield('form-content')
                </div>

                <div class="foot-info">
                    @yield('foot-info')
                </div>
            </div>
        </main>
    </div>
</body>