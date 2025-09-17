<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Cuti</title>
</head>
<body>
    <h1>Status Permohonan Cuti Anda</h1>
    <p>Halo, {{ $permohonan->karyawan->nama }},</p>
    <p>Permohonan cuti Anda untuk jenis cuti <strong>{{ $permohonan->jenis_cuti }}</strong> dengan tanggal mulai <strong>{{ $permohonan->tanggal_mulai }}</strong> dan tanggal selesai <strong>{{ $permohonan->tanggal_selesai }}</strong> telah diperbarui.</p>
    <p>Status terbaru: <strong>{{ $permohonan->status }}</strong></p>

    @if ($permohonan->status === 'Ditolak' && $permohonan->alasan_penolakan)
        <p>Alasan penolakan: <strong>{{ $permohonan->alasan_penolakan }}</strong></p>
    @endif

    <p>Terima kasih.</p>
</body>
</html>
