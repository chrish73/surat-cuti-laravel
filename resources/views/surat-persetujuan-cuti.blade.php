<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Persetujuan Cuti</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img { /* CSS baru untuk logo */
            width: 100px; /* Sesuaikan ukuran logo */
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        .content {
            margin-bottom: 20px;
        }
        .content table{
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .content table td{
            padding: 5px;
            border: 1px solid #ddd;
        }
        .content p {
            line-height: 1.5;
            margin-bottom: 10px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .signature {
            margin-top: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SURAT PERSETUJUAN CUTI</h1>
    </div>

    <div class="content">
        <p>Dengan hormat,</p>
        <p>Menindaklanjuti permohonan cuti yang diajukan oleh karyawan di bawah ini:</p>
        <table>
            <tr>
                <td>Nama</td>
                <td>: {{ $karyawan->nama }}</td>
            </tr>
            <tr>
                <td>ID Karyawan</td>
                <td>: {{ $karyawan->id_karyawan }}</td>
            </tr>
            <tr>
                <td>Unit</td>
                <td>: {{ $karyawan->unit }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>: {{ $karyawan->email }}</td>
            </tr>
            <tr>
                <td>Tanggal Pengajuan</td>
                <td>: {{ \Carbon\Carbon::parse($permohonan->created_at)->format('d F Y') }}</td>
            </tr>
        </table>
        <p>Dengan ini kami menyatakan permohonan cuti Anda <strong>Disetujui</strong>.</p>
        <p>Detail cuti adalah sebagai berikut:</p>
        <div class="details">
            <table>
                <tr>
                    <td>Jenis Cuti</td>
                    <td>: {{ $permohonan->jenis_cuti }}</td>
                </tr>
                <tr>
                    <td>Tanggal Mulai</td>
                    <td>: {{ $permohonan->tanggal_mulai }}</td>
                </tr>
                <tr>
                    <td>Tanggal Selesai</td>
                    <td>: {{ $permohonan->tanggal_selesai }}</td>
                </tr>
                <tr>
                    <td>Durasi</td>
                    <td>: {{ $permohonan->durasi }} hari</td>
                </tr>
                <tr>
                    <td>Sisa Cuti Tahunan</td>
                    <td>: {{ $karyawan->jatah_cuti_tahunan }} hari</td>
                </tr>
                <tr>
                    <td>Alasan Cuti</td>
                    <td>: {{ $permohonan->alasan }}</td>
                </tr>
                <tr>
                    <td>Alamat Selama Cuti</td>
                    <td>: {{ $permohonan->alamat_selama_cuti }}</td>
                </tr>
            </table>
        </div>
        <p>Demikian surat persetujuan cuti ini dibuat untuk digunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature">
        <p>Medan, {{ $tanggal_cetak }}</p>
        <p>Mengetahui,</p>
        <p>MGR {{ $karyawan->unit }} Supervision</p>
        <p style="margin-top: 60px;">{{ $nama_manajer }}</p>
        <p>NIK. {{ $id_manajer }}</p>
    </div>
</body>
</html>