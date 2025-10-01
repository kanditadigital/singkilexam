<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perengkingan Ujian</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1f2933;
            margin: 24px;
        }
        h2 {
            margin: 0 0 4px;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .meta {
            margin-bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px 24px;
            font-size: 11px;
            color: #3e4c59;
        }
        .meta div strong {
            color: #1f2933;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: #f1f5f8;
        }
        th, td {
            border: 1px solid #d8e0e8;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        td {
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .muted {
            color: #62748a;
            font-size: 10px;
        }
        .top-rank {
            background: linear-gradient(90deg, rgba(252,233,160,0.55), rgba(255,255,255,0));
        }
        footer {
            margin-top: 24px;
            font-size: 10px;
            color: #52606d;
            text-align: right;
        }
    </style>
</head>
<body>
    <header>
        <h2>Perengkingan Ujian</h2>
        <div class="meta">
            <div><strong>Ujian:</strong> {{ $exam->exam_name ?? '-' }}</div>
            <div><strong>Kode:</strong> {{ $exam->exam_code ?? '-' }}</div>
            <div><strong>Peserta:</strong> {{ $participantLabel }}</div>
            <div><strong>Cabdin:</strong> {{ $branch->branch_name ?? 'Semua' }}</div>
            <div><strong>Sekolah:</strong> {{ $school->school_name ?? 'Semua' }}</div>
            <div><strong>Total Data:</strong> {{ $items->count() }}</div>
        </div>
    </header>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 36px;">#</th>
                <th>Nama Peserta</th>
                <th>Asal Sekolah</th>
                <th>Cabdin</th>
                <th class="text-right" style="width: 80px;">Nilai</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr class="{{ $loop->index < 3 ? 'top-rank' : '' }}">
                <td class="text-center">{{ $item['rank'] }}</td>
                <td>
                    <div><strong>{{ $item['participant_name'] }}</strong></div>
                    <div class="muted">{{ $item['participant_label'] }} | {{ $item['participant_identifier'] }}</div>
                </td>
                <td>{{ $item['school_name'] }}</td>
                <td>{{ $item['branch_name'] }}</td>
                <td class="text-right"><strong>{{ $item['score_formatted'] }}</strong></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <footer>
        Dicetak pada {{ $generatedAt->format('d M Y H:i') }} WIB
    </footer>
</body>
</html>
