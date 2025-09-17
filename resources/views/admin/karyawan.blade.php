<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Karyawan - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #333;
        }
        .add-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #555;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
        }
        .edit-btn {
            background-color: #007bff;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-btn:hover,
        .close-btn:focus {
            color: #000;
            text-decoration: none;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group .checkbox-container {
            display: flex;
            align-items: center;
        }
        .form-group .checkbox-container input {
            width: auto;
            margin-right: 10px;
        }
        .form-buttons {
            text-align: right;
        }
        .form-buttons button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
        }
        #submit-btn {
            background-color: #28a745;
        }
        .notification-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px 40px;
            border-radius: 10px;
            z-index: 1000;
            display: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="/admin/dashboard" class="logo-link">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
        </a>
        <a href="/admin/permohonan" class="nav-item">
            <span class="icon">
            <img src="{{ asset('images/mail-cuti.png') }}" alt="Permohonan Cuti Icon">
            </span>
            <span class="text">Permohonan Cuti</span>
        </a>
        <a href="/admin/karyawan" class="nav-item active">
            <span class="icon">
                <img src="{{ asset('images/data-karyawan.png') }}" alt="Data Karyawan Icon">
            </span>
            <span class="text">Data Karyawan</span>
        </a>
        <a href="#" class="nav-item" id="logout-btn">
            <span class="icon">
            <img src="{{ asset('images/logout.png') }}" alt="Logout Icon">
            </span>
            <span class="text">Keluar</span>
        </a>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Manajemen Karyawan</h1>
                <button class="add-btn" id="add-karyawan-btn">Tambah Karyawan Baru</button>
            </div>
            <div class="table-container">
                <table id="karyawan-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>ID Karyawan</th>
                            <th>Email</th>
                            <th>Unit</th>
                            <th>Jatah Cuti Tahunan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="karyawan-list">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="karyawan-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="close-karyawan-modal">&times;</span>
            <h2 id="modal-title">Tambah Karyawan</h2>
            <form id="karyawan-form">
                <input type="hidden" id="karyawan-id">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" id="nama" required>
                </div>
                <div class="form-group">
                    <label for="id_karyawan">ID Karyawan</label>
                    <input type="text" id="id_karyawan" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="unit">Unit</label>
                    <input type="text" id="unit" required>
                </div>
                <div class="form-group">
                    <label for="jatah_cuti_tahunan">Jatah Cuti Tahunan</label>
                    <input type="number" id="jatah_cuti_tahunan" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" required>
                    <small>Kosongkan password jika tidak ingin mengubahnya.</small>
                </div>
                <div class="form-group">
                    <div class="checkbox-container">
                        <input type="checkbox" id="is_admin">
                        <label for="is_admin">Apakah admin?</label>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="submit" id="submit-btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="notification-popup" class="notification-popup">
        <span id="popup-message"></span>
    </div>

    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
