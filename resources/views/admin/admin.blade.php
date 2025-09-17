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
    <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="top-left-logo">

    <header>
        <h2>Data cuti karyawan</h2>
    </header>

    <main>
        <h3>Permohonan Cuti Baru</h3>
        {{-- <p class="info-text">Kelola permohonan cuti langsung dari tabel di bawah ini.</p> --}}
        <p class="info-text">Pilih terlebih dahulu unit yang ingin diexport (Default : Semua Unit)!</p>

        <div class="filter-container">
    <label for="unit-filter">Filter Berdasarkan Unit:</label>
    <select id="unit-filter">
        <option value="">Semua Unit</option>
        <option value="FBB Assurance">FBB Assurance</option>
        <option value="Service Node">Service Node</option>
        <option value="FBB Fulfillment">FBB Fulfillment</option>
        <option value="BGES Assurance">BGES Assurance</option>
        <option value="BGES Fulfillment">BGES Fulfillment</option>
        <option value="Performance">Performance</option>
        <option value="Surveillance">Surveillance</option>
        <option value="HD DEFA">HD DEFA</option>
        <option value="Wifi">Wifi</option>
    </select>
    <button id="export-excel-btn" class="action-btn">Ekspor ke Excel</button>
</div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>ID Karyawan</th>
                        <th>Unit</th> <th>Jenis Cuti</th>
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

<div id="reject-modal" class="popup-modal">
    <div class="popup-content">
        <span id="close-reject-modal" class="close-btn">&times;</span>
        <h4 id="popup-title">Tolak Permohonan</h4>
        <p>Silakan masukkan alasan penolakan:</p>
        <textarea id="rejection-reason" rows="4" class="form-control" required></textarea>
        <button id="confirm-reject-btn" class="action-btn">Kirim Penolakan</button>
    </div>
</div>

<script src="{{ asset('js/gaya.js') }}"></script>

</body>
</html>
