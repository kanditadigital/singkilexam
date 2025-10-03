<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $title }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/all.min.css') }}">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #0f4c75 0%, #113f67 50%, #0d1b2a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Poppins', sans-serif;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(12, 34, 64, 0.25);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .auth-head {
            background: linear-gradient(120deg, #113f67, #30475e);
            color: #fff;
            padding: 36px 32px;
            position: relative;
        }
        .auth-head::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.15), transparent 55%);
        }
        .auth-head h1 {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .auth-head p {
            position: relative;
            z-index: 1;
            margin: 12px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .auth-body {
            padding: 32px;
        }
        .form-group {
            margin-bottom: 22px;
        }
        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1f2d3d;
            margin-bottom: 8px;
        }
        .input-icon {
            position: relative;
        }
        .input-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .input-icon .form-control {
            padding-left: 46px;
        }
        .form-control {
            border-radius: 12px;
            border: 2px solid #e1e5ea;
            padding: 14px 16px;
            font-size: 14px;
            transition: all .2s ease;
        }
        .form-control:focus {
            border-color: #113f67;
            box-shadow: 0 0 0 0.2rem rgba(17, 63, 103, 0.15);
        }
        .btn-login {
            width: 100%;
            border-radius: 12px;
            border: none;
            padding: 14px 0;
            font-weight: 600;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            background: linear-gradient(120deg, #113f67, #0f3057);
            color: #fff;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 20px rgba(17, 63, 103, 0.25);
        }
        .alert {
            border-radius: 12px;
            border: none;
            padding: 12px 16px;
            font-size: 14px;
        }
        .alert-danger {
            background: linear-gradient(120deg, #ff6b6b, #ee5253);
            color: #fff;
        }
    </style>
</head>
<body>
    @include('sweetalert::alert')
    <div class="auth-card">
        <div class="auth-head">
            <h1>Panel Cabdin</h1>
            <p>Masuk untuk mengelola sekolah dan siswa di wilayah Anda.</p>
        </div>
        <div class="auth-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('cabdin.signin') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Email Cabdin</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="nama@cabdin.go.id" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi" required>
                    </div>
                </div>
                <div class="form-group d-flex justify-content-between align-items-center">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="remember">Ingat saya</label>
                    </div>
                    <a href="{{ route('signin.form') }}" class="small text-muted">Login Admin</a>
                </div>
                <button type="submit" class="btn btn-login">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>
