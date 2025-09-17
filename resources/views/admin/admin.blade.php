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

        <!-- Table -->
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
                        <th>Edit</th>
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

<!-- Notification Popup -->
<div id="notification-popup" class="popup-overlay">
    <div class="popup-content">
        <div id="popup-icon" class="popup-icon"></div>
        <h4 id="popup-title"></h4>
        <p id="popup-message"></p>
        <button id="popup-close-btn" class="action-btn">Tutup</button>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="popup-modal">
    <div class="popup-content">
        <span id="close-reject-modal" class="close-btn">&times;</span>
        <h4 id="popup-title">Tolak Permohonan</h4>
        <p>Silakan masukkan alasan penolakan:</p>
        <textarea id="rejection-reason" rows="4" class="form-control" required></textarea>
        <button id="confirm-reject-btn" class="action-btn">Kirim Penolakan</button>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="popup-modal">
    <div class="popup-content">
        <span id="close-edit-modal" class="close-btn">&times;</span>
        <h4 id="popup-title">Edit Permohonan Cuti</h4>
        <form id="edit-form">
            <input type="hidden" id="edit-permohonan-id">

            <div class="form-group">
                <label for="edit-jenis-cuti">Jenis Cuti</label>
                <input type="text" id="edit-jenis-cuti" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-tanggal-mulai">Tanggal Mulai</label>
                <input type="date" id="edit-tanggal-mulai" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-tanggal-selesai">Tanggal Selesai</label>
                <input type="date" id="edit-tanggal-selesai" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-durasi">Durasi (Hari)</label>
                <input type="number" id="edit-durasi" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit-alasan">Alasan Cuti</label>
                <textarea id="edit-alasan" rows="4" class="form-control" required></textarea>
            </div>

            <div class="form-group">
                <label for="edit-alamat">Alamat Selama Cuti</label>
                <textarea id="edit-alamat" rows="4" class="form-control" required></textarea>
            </div>

            <button type="submit" class="action-btn">Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script src="{{ asset('js/gaya.js') }}"></script>

</body>
</html>
