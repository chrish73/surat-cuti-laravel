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
    <!-- Logo -->
    <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="top-left-logo">

    <!-- Header -->
    <header>
        <h2>Data Cuti Karyawan</h2>
    </header>

    <!-- Main Content -->
    <main>
        <h3>Permohonan Cuti Baru</h3>
        <p class="info-text">
            Pilih terlebih dahulu unit yang ingin diekspor (Default: Semua Unit)!
        </p>

        <!-- Filter -->
<div class="filter-container">
    <div class="filter-group">
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
    </div>

    <div class="filter-group">
        <label for="search-name">Cari Nama Karyawan:</label>
        <input type="text" id="search-name" placeholder="Masukkan nama...">
    </div>
    
    <div class="filter-group">
        <label for="start-date-filter">Filter Tanggal Mulai (Dari):</label>
        <input type="date" id="start-date-filter">
    </div>
    <div class="filter-group">
        <label for="end-date-filter">Filter Tanggal Selesai (Hingga):</label>
        <input type="date" id="end-date-filter">
    </div>

    <div class="action-buttons-stack">
        <button id="export-excel-btn" class="action-btn">Ekspor ke Excel</button>
        <a href="/admin/karyawan" class="data-karyawan-link">
            <p>Data Karyawan</p>
        </a>
    </div>
</div>

        <!-- Tabel Data -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>ID Karyawan</th>
                        <th>Unit</th>
                        <th>Jenis Cuti</th>
                        <th>Sisa Cuti Tahunan</th>
                        <th>Tanggal Mulai</th>
                        <th>Durasi</th>
                        <th>Alasan & Alamat Cuti</th>
                        <th>Status</th>
                        <th>Lampiran File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="request-list"></tbody>
            </table>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Administrasi Cuti Karyawan</p>
    </footer>
</div>

<!-- Notifikasi -->
<div id="notification-popup" class="popup-overlay">
    <div class="popup-content">
        <div id="popup-icon" class="popup-icon"></div>
        <h4 id="popup-title"></h4>
        <p id="popup-message"></p>
        <button id="popup-close-btn" class="action-btn">Tutup</button>
    </div>
</div>

<!-- Modal Tolak Cuti -->
<div id="reject-modal" class="popup-modal">
    <div class="popup-content">
        <span id="close-reject-modal" class="close-btn">&times;</span>
        <h4 id="popup-title">Tolak Permohonan</h4>
        <p>Silakan masukkan alasan penolakan:</p>
        <textarea id="rejection-reason" rows="4" class="form-control" required></textarea>
        <button id="confirm-reject-btn" class="action-btn action-btn-reject">
            <span id="button-text">Kirim Penolakan</span>
            <span id="loading-spinner" class="spinner hidden"></span>
        </button>
    </div>
</div>

<div id="loading-overlay" class="loading-overlay hidden">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p>Harap Ditunggu...</p>
    </div>
</div>


<!-- JavaScript -->
<script src="{{ asset('js/gaya.js') }}"></script>

</body>
</html>
