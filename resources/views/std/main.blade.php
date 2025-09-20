<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $title }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/exam.css') }}">
</head>
<body>

    @include('std.navbar')

    <div class="container">
        @include('sweetalert::alert')
        @yield('content')
    </div>

    <script src="{{ asset('vendor/stislaravel/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>
