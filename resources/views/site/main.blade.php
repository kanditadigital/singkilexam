<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta dasar -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Title -->
    <title>{{ $title }} &mdash; Kandita Digital Integrasi</title>

    <!-- SEO Meta Tags (Manual) -->
    <meta name="description" content="Kandita Digital Integrasi adalah penyedia solusi digital marketing, pengembangan website, SEO, dan integrasi sistem untuk membantu bisnis Anda tumbuh lebih cepat.">
    <meta name="keywords" content="digital marketing, website development, SEO, integrasi sistem, Kandita Digital Integrasi, jasa pembuatan website, optimasi SEO, digital agency Indonesia">
    <meta name="author" content="Kandita Digital Integrasi">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }} &mdash; Kandita Digital Integrasi">
    <meta property="og:description" content="Kandita Digital Integrasi membantu bisnis Anda dengan layanan digital marketing, website, SEO, dan integrasi sistem.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('storage/kandita.webp') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }} &mdash; Kandita Digital Integrasi">
    <meta name="twitter:description" content="Kandita Digital Integrasi - Solusi digital marketing, website, SEO, dan integrasi sistem.">
    <meta name="twitter:image" content="{{ asset('storage/kandita.webp') }}">

    <!-- Preconnect fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('storage/kandita.webp') }}" type="image/x-icon">

    <!-- Styles -->
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-poppins text-gray-800 antialiased">

    @include('site.navbar')

    {{-- Notifikasi (alert sederhana) --}}
    @if(session('toast'))
        <div class="max-w-lg mx-auto mt-4">
            <div class="px-4 py-3 rounded
                        {{ session('toast.type') === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white' }}">
                {{ session('toast.message') }}
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="{{ asset('vendor/stislaravel/js/jquery-3.7.1.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
