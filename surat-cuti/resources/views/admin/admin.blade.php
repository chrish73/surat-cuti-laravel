<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Halaman Admin - Persetujuan Cuti</title>
    <link rel="stylesheet" href="{{ asset('css/stel.css') }}">
</head>
<body>

<div class="container">
    {{-- Logo ditambahkan di sini --}}
    <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="top-left-logo">

    <header>
        <h2>Dashboard Admin Cuti</h2>
    </header>

    <main>
        <h3>Permohonan Cuti Baru</h3>
        <p class="info-text">Kelola permohonan cuti langsung dari tabel di bawah ini.</p>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>ID Karyawan</th>
                        <th>Jenis Cuti</th>
                        <th>Sisa Cuti Tahunan</th>
                        <th>Tanggal Mulai</th>
                        <th>Durasi</th>
                        <th>Alasan & Alamat Cuti</th>
                        <th>Status</th>
                        <th>Lampiran File</th>
                        <th>Aksi</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody id="request-list">
                    </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Administrasi Cuti Karyawan</p>
    </footer>
</div>

<div id="notification-popup" class="popup-overlay">
  <div class="popup-content">
    <div id="popup-icon" class="popup-icon"></div>
    <h4 id="popup-title"></h4>
    <p id="popup-message"></p>
    <button id="popup-close-btn" class="action-btn">Tutup</button>
  </div>
</div>

<script src="{{ asset('js/gaya.js') }}"></script>

</body>
</html>
