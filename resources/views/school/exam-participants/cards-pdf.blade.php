<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kartu Peserta Ujian</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 20px;
      background: #f8f9fa;
    }
    .card {
      width: 48%;
      min-height: 160px;
      float: left;
      margin: 1%;
      border: 1px solid #000;
      border-radius: 6px;
      padding: 10px;
      box-sizing: border-box;
      background: #fff;
      page-break-inside: avoid;
      position: relative;
    }
    .card-header {
      text-align: center;
      font-weight: bold;
      font-size: 13px;
      margin-bottom: 6px;
    }
    .logo {
      position: absolute;
      top: 8px;
      left: 8px;
      width: 40px;
    }
    .qrcode {
      position: absolute;
      top: 8px;
      right: 8px;
      width: 40px;
      height: 40px;
      background: #eee;
    }
    .info {
      margin-top: 10px;
      font-size: 11px;
      line-height: 1.5;
    }
    .info strong {
      display: inline-block;
      width: 120px;
    }
    .footer {
      font-size: 9px;
      text-align: center;
      margin-top: 8px;
      border-top: 1px solid #ccc;
      padding-top: 4px;
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
    <div class="card-header">
      KARTU LOGIN UJIAN<br>
      {{ $participant['exam_name'] }} {{ date('Y') }}
    </div>
    <hr>

    <div class="info">
      <div><strong>Nama Peserta</strong>: {{ $participant['name'] }}</div>
      <div><strong>Username</strong>: {{ $participant['identifier'] }}</div>
      <div><strong>Password</strong>: {{ $participant['identifier'] }}*</div>
    </div>

    <div class="footer">
      Dicetak pada {{ $generatedAt->format('d M Y H:i') }} WIB
    </div>
  </div>
  @endforeach
</body>
</html>
