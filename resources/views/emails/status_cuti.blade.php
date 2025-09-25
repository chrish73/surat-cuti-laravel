<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Status Cuti</title>
</head>
<body>
    <h1>Status Permohonan Cuti Anda</h1>
    <p>Halo, {{ $permohonan->karyawan->nama }},</p>
    <p>Status terbaru: <strong>{{ $permohonan->status }}</strong></p>
    <p>Kami ingin memberitahukan bahwa permohonan cuti Anda untuk jenis cuti <strong>{{ $permohonan->jenis_cuti }}</strong> dengan tanggal mulai <strong>{{ $permohonan->tanggal_mulai }}</strong> dan tanggal selesai <strong>{{ $permohonan->tanggal_selesai }}</strong> telah diperbarui.</p>
    <P><i>Surat persetujuan bisa dilihat di Riwayat Cuti pada Website Surat Permohonan Cuti!</i> </P>

    @if ($permohonan->status === 'Ditolak' && $alasan_penolakan)
        <p>Alasan penolakan: <strong>{{ $alasan_penolakan }}</strong></p>
    @endif

    <p>Terima kasih.</p>
</body>
</html>
