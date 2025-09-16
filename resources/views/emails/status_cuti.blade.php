<!DOCTYPE html>
<html>
<head>
    <title>Pemberitahuan Status Cuti</title>
</head>
<body>
    <h1>Pemberitahuan Status Permohonan Cuti Anda</h1>
    <p>Yth. {{ $permohonan->karyawan->nama_karyawan }},</p>
    <p>Kami ingin memberitahukan bahwa permohonan cuti Anda telah **{{ $permohonan->status }}**.</p>
    <p>Berikut adalah detail permohonan Anda:</p>
    <ul>
        <li>**Tanggal Pengajuan:** {{ \Carbon\Carbon::parse($permohonan->tanggal_pengajuan)->translatedFormat('d F Y') }}</li>
        <li>**Tanggal Mulai:** {{ \Carbon\Carbon::parse($permohonan->tanggal_mulai_cuti)->translatedFormat('d F Y') }}</li>
        <li>**Tanggal Selesai:** {{ \Carbon\Carbon::parse($permohonan->tanggal_selesai_cuti)->translatedFormat('d F Y') }}</li>
        <li>**Alasan:** {{ $permohonan->alasan_cuti }}</li>
        <li>**Status:** {{ $permohonan->status }}</li>
    </ul>

    @if($permohonan->status === 'Ditolak')
        <p>Silakan hubungi admin untuk informasi lebih lanjut mengenai alasan penolakan.</p>
    @endif

    <p>Terima kasih.</p>
</body>
</html>
