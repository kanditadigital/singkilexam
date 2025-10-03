<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }} &mdash; Kandita Digital Integrasi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>


    @include('site.navbar')
    {{-- Notifikasi (alert sederhana) --}}
    @if(session('toast'))
        <div class="max-w-lg mx-auto mt-4">
            <div class="px-4 py-3 rounded
                        {{ session('toast.type') === 'success' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                {{ session('toast.message') }}
            </div>
        </div>
    @endif
    @yield('content')

    <script src="{{ asset('vendor/stislaravel/js/jquery-3.7.1.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
