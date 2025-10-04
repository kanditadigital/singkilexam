<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $title ?? 'Login' }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Bootstrap & Fontawesome --}}
    <link rel="stylesheet" href="{{ asset('vendor/stislaravel/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/stislaravel/css/all.min.css') }}">
    <link rel="shortcut icon" href="{{ asset('storage/kandita.webp') }}" type="image/x-icon">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a2342, #19376d, #0f4c75);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .login-left {
            flex: 1;
            background: url('/storage/login.webp') no-repeat center center;
            background-size: cover;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            position: relative;
        }

        .login-left::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .login-left > * {
            position: relative;
            z-index: 2; /* agar konten tampil di atas overlay */
        }


        .login-left img {
            max-width: 90%;
            height: auto;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right .icon-user {
            margin-bottom: 0px;
        }

        .login-right h2 {
            font-weight: 700;
            color: #19376d;
            margin-bottom: 10px;
        }

        .login-right p {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 10px;
            padding: 14px 18px 14px 45px;
            border: 1px solid #ccc;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #19376d;
            box-shadow: 0 0 0 3px rgba(25,55,109,0.2);
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #888;
        }

        .btn-login {
            background: linear-gradient(135deg, #0a2342, #19376d);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            box-shadow: 0 8px 20px rgba(25,55,109,0.3);
            transform: translateY(-2px);
            color: white;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .form-footer a {
            color: #19376d;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                min-height: auto;
            }

            .login-left {
                display: none;
            }

            .login-right {
                padding: 40px 25px;
            }
        }
    </style>
</head>
<body>
    @include('sweetalert::alert')

    <div class="login-wrapper">
        {{-- Bagian kiri dengan ilustrasi --}}
        <div class="login-left">

        </div>

        {{-- Bagian kanan dengan form --}}
        <div class="login-right">
            <div class="text-center">
                <div class="icon-user">
                    <img src="{{ asset('storage/kandita.webp') }}" class="img-fluid" width="80" alt="Examdita by Kandita">
                </div>
                <h2>Selamat Datang</h2>
                <p>Silakan masuk untuk melanjutkan</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="post">
                @csrf
                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="email" class="form-control" placeholder="Username / Email" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                </div>

                <div class="form-footer">
                    <div>
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" class="ml-1">Ingat saya</label>
                    </div>
                    <a href="#">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-login mt-4">LOGIN</button>
            </form>
        </div>
    </div>

    {{-- Script --}}
    <script src="{{ asset('vendor/stislaravel/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/bootstrap.min.js') }}"></script>
</body>
</html>
