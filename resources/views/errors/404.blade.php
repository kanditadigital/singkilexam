<!DOCTYPE html>
<html lang="en">
<head>
    <title>404 - Halaman Tidak Ditemukan</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('vendor/stislaravel/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/stislaravel/css/all.min.css') }}">
    <link rel="shortcut icon" href="{{ asset('storage/kandita.webp') }}" type="image/x-icon">

    <style>
        body {
            background: linear-gradient(135deg, #0a2342, #19376d, #0f4c75);
            color: white;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            line-height: 1;
        }

        .error-message {
            font-size: 1.3rem;
            margin-bottom: 25px;
            opacity: 0.9;
        }

        .btn-home {
            background: white;
            color: #19376d;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 10px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: #f1f1f1;
            text-decoration: none;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h2>Halaman Tidak Ditemukan</h2>
        <p class="error-message">Maaf, halaman yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
        <a href="{{ url('/') }}" class="btn-home"><i class="fas fa-home mr-2"></i> Kembali ke Beranda</a>
    </div>
</body>
</html>
