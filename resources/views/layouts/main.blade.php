<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $title }} &mdash; Stislaravel by Kandita</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/components.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/stislaravel/css/all.min.css') }}">

    <link rel="shortcut icon" href="{{ asset('storage/kandita.webp') }}" type="image/x-icon">

</head>
<body>

    <div id="app">
        <div class="main-wrapper">

            @include('layouts.header')
            @include('layouts.sidebar')

            <div class="main-content">
                <div class="mt-3">
                    @include('sweetalert::alert')
                    @yield('content')
                </div>
            </div>

            @include('layouts.footer')

        </div>
    </div>

    <script src="{{ asset('vendor/stislaravel/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/scripts.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('vendor/stislaravel/js/stisla.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tiny.cloud/1/9xs1wwpbsgxm69hod0co0bhs7u8rfrqrozq4cfki9kkp7c1b/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    @stack('scripts')
</body>
</html>
