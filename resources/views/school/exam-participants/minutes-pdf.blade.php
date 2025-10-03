<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Ujian</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 20mm;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .exam-info {
            margin-bottom: 20px;
        }
        .exam-info div {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .signature {
            margin-top: 50px;
            text-align: center;
        }
        .signature div {
            margin-bottom: 60px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Berita Acara Ujian</div>
        <div>{{ $school->school_name }}</div>
    </div>

    <div class="exam-info">
        <div><strong>Nama Ujian:</strong> {{ $exam->exam_name }}</div>
        <div><strong>Kode Ujian:</strong> {{ $exam->exam_code }}</div>
        <div><strong>Tanggal:</strong> {{ $exam->created_at->format('d F Y') }}</div>
        <div><strong>Jumlah Peserta:</strong> {{ $participants->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Nama Peserta</th>
                <th width="20%">Jenis Peserta</th>
                <th width="20%">Identitas</th>
                <th width="15%">Info Tambahan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $index => $participant)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $participant['name'] }}</td>
                <td>{{ $participant['type'] }}</td>
                <td>{{ $participant['identifier'] }}</td>
                <td>{{ $participant['meta'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div>Mengetahui,</div>
        <div>Kepala Sekolah</div>
        <div>{{ $school->school_name }}</div>
    </div>

    <div class="footer">
        Dicetak pada {{ $generatedAt->format('d M Y H:i') }} WIB
    </div>
</body>
</html>
