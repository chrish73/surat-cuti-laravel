<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Halaman Admin - Data Karyawan</title>
 <link rel="stylesheet" href="{{ asset('css/datel.css') }}">
</head>
<body>

<div class="container">
    <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="top-left-logo">
    <header>
        <h2>Data Karyawan</h2>
    </header>

    <main>
        <div class="card-form">
            <h3 id="form-title">Tambah Karyawan Baru</h3>
            <form id="karyawan-form">
                <input type="hidden" id="karyawan-id">
                <div class="form-group">
                    <label for="nama">Nama Karyawan</label>
                    <input type="text" id="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="id_karyawan">Perner Karyawan / NIK</label>
                    <input type="text" id="id_karyawan" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="unit">Unit</label>
                    <select id="unit" class="form-control" required>
                        <option value="">Pilih Unit</option>
                        <option value="FBB Assurance">FBB Assurance</option>
                        <option value="Service Node">Service Node</option>
                        <option value="FBB Fulfillment">FBB Fulfillment</option>
                        <option value="BGES Assurance">BGES Assurance</option>
                        <option value="BGES Fulfillment">BGES Fulfillment</option>
                        <option value="Performance">Performance</option>
                        <option value="Surveillance">Surveillance</option>
                        <option value="HD DEFA">HD DEFA</option>
                        <option value="HD CCAN">HD CCAN</option>
                        <option value="HD WIFI">HD WIFI</option>
                        <option value="WIFI FFM & ASC">WIFI FFM & ASC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jatah_cuti_tahunan">Jatah Cuti Tahunan (Hari)</label>
                    <input type="number" id="jatah_cuti_tahunan" class="form-control" min="0" required>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_admin">
                    <label for="is_admin">Jadikan sebagai Admin?</label>
                </div>
                <div class="form-group" id="password-group" style="display:none;">
                    <label for="password">Password</label>
                    <input type="password" id="password" class="form-control">
                </div>
                <button type="submit" class="action-btn">Simpan Data</button>
                <button type="button" id="cancel-edit-btn" class="action-btn" style="display:none;">Batal Edit</button>
            </form>
        </div>

        <h3>Daftar Karyawan</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Perner</th>
                        <th>Nama</th>
                        <th>Unit</th>
                        <th>Email</th>
                        <th>Jatah Cuti</th>
                        <th>Admin?</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="karyawan-list">
                    
                </tbody>
            </table>
        </div>
    </main>

    <div id="delete-modal" class="popup-modal">
        <div class="popup-content">
            <span id="close-delete-modal" class="close-btn">&times;</span>
            <h4>Konfirmasi Hapus</h4>
            <p>Anda yakin ingin menghapus data karyawan ini?</p>
            <button id="confirm-delete-btn" class="action-btn">Hapus</button>
            <button id="cancel-delete-btn" class="action-btn">Batal</button>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Administrasi Cuti Karyawan</p>
    </footer>
</div>

<script src="{{ asset('js/data-karyawan.js') }}"></script>

</body>
</html>