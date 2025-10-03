<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Peserta Ujian</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .card {
            border: 1px solid #000;
            width: 48%;
            height: 120px;
            float: left;
            margin: 1%;
            padding: 10px;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .card:nth-child(odd) {
            margin-right: 0;
        }
        .card:nth-child(even) {
            margin-left: 0;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .exam-info {
            font-size: 9px;
            margin-bottom: 5px;
        }
        .participant-info {
            font-size: 10px;
        }
        .footer {
            margin-top: 10px;
            font-size: 8px;
            text-align: center;
            color: #666;
        }
        @page {
            margin: 10mm;
        }
    </style>
</head>
<body>
    @foreach($participants as $participant)
    <div class="card">
        <div class="header">KARTU PESERTA UJIAN</div>
        <div class="exam-info">
            <strong>Ujian:</strong> {{ $participant['exam_name'] }}<br>
            <strong>Kode:</strong> {{ $participant['exam_code'] }}<br>
            <strong>Sekolah:</strong> {{ $participant['school_name'] }}
        </div>
        <div class="participant-info">
            <strong>Nama:</strong> {{ $participant['name'] }}<br>
            <strong>{{ $participant['type'] }}:</strong> {{ $participant['identifier'] }}
        </div>
        <div class="footer">
            Dicetak pada {{ $generatedAt->format('d M Y H:i') }} WIB
        </div>
    </div>
    @endforeach
</body>
</html>
