<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kartu Peserta Ujian - {{ $exam->exam_name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.4;
        }
        .card-container {
            overflow: hidden; /* Membersihkan float */
            width: 100%;
        }
        .card {
            float: left;
            width: 8cm;
            height: 4cm;
            border: 1px solid #000;
            padding: 0.5cm;
            box-sizing: border-box;
            margin: 0 0.5cm 0.5cm 0;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            .card {
                margin: 0 0.25cm 0.25cm 0; /* Gap lebih kecil untuk print */
            }
        }
        .card-header {
            text-align: center;
            border-bottom: 1px solid #000;
            margin-bottom: 0.3cm;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.1cm; /* Jarak antara gambar dan teks */
            position: relative; /* Jika perlu posisi absolute di masa depan */
        }
        .card-header img {
            width: 1.2cm; /* Ukuran gambar */
            height: 1.2cm;
            object-fit: contain; /* Agar gambar tidak terdistorsi */
            position: absolute;
            top: -8px;
            /* Hapus position: absolute; agar mengikuti flow flex */
        }
        .card-header h4 {
            font-size: 14px;
            margin: 0 0 0.2cm 0;
            font-weight: bold;
        }
        .card-header h5 {
            font-size: 12px;
            margin: 0;
            font-weight: normal;
        }
        .card-body {
            flex: 1;
            display: flex;
            align-items: center;
        }
        .card-content {
            width: 100%;
            display: flex;
            gap: 0.5cm;
        }
        .info-section {
            flex: 1;
        }
        .info-section table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
        }
        .info-section table tr td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .info-section table tr td:first-child {
            font-weight: bold;
            width: 35%;
            white-space: nowrap;
        }
        .info-section table tr td:nth-child(2) {
            width: 5%;
            text-align: center;
        }
        .info-section table tr td:last-child {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="card-container">
        @foreach($participants as $index => $participant)
            @if($index % 2 == 0 && $index > 0)
                <div style="clear: both;"></div>
            @endif
            <div class="card">
                <div class="card-header">
                    <img src="{{ public_path('storage/img/th.png') }}" alt="Logo Kop"> <!-- Path URL yang benar untuk semua card -->
                    <h4>KARTU PESERTA UJIAN</h4>
                    <h5 style="margin-top: -5px;"><strong>{{ $participant['exam_name'] }}</strong></h5>
                </div>
                <div class="card-body">
                    <div class="card-content">
                        <div class="info-section">
                            <table>
                                <tr><td>Nama Peserta</td><td>:</td><td>{{ $participant['name'] }}</td></tr>
                                <tr><td>Asal Sekolah</td><td>:</td><td>{{ $participant['school_name'] }}</td></tr>
                                <tr><td>Username</td><td>:</td><td>{{ $participant['identifier'] }}</td></tr>
                                <tr><td>Password</td><td>:</td><td>{{ $participant['password'] }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div style="clear: both;"></div>
    </div>
</body>
</html>
