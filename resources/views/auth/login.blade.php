<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>

    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/exam.css') }}">

    <link rel="shortcut icon" href="{{ asset('storage/kandita.webp') }}" type="image/x-icon">
</head>
<body>

    <div class="navbar-exam">
        <div class="container"></div>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card" style="border: 0;">
                    <div class="card-body p-5">
                        <div class="header-login mb-4">
                            <h3>Selamat Datang</h3>
                            <p style="font-size: 14px;">Silahkan login dengan Username dan Password yang telah anda miliki</p>
                        </div>
                        <form action="{{ route('bro.auth') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Username" autocomplete="off" autofocus>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="off">
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-block btn-exam">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/stislaravel/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
