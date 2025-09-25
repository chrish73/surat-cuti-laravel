<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Permohonan Cuti</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <main class="page-content">
        <div class="card-logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Infranexia by Telkom Indonesia Logo">
        </div>

        <div class="card-container">
            <div class="card">
                <div class="employee-info">
                    <div class="profile-pic">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="info-details">
                        <div class="info-item">
                            <span class="label">Nama:</span>
                            <span class="value" id="employee-name"></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Perner:</span>
                            <span class="value" id="employee-id"></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Unit:</span>
                            <span class="value" id="employee-unit"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Permohonan Cuti</h2>
                    <form id="leave-form" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="leave-type">Jenis Cuti</label>
                                <select id="leave-type" class="form-control">
                                    <option>Cuti Tahunan</option>
                                    <option>Cuti Lahiran</option>
                                    <optgroup label="Cuti Alasan Penting">
                                        <option>Cuti Menikah</option>
                                        <option>Cuti Sakit</option>
                                        <option>Cuti Istri Lahiran</option>
                                        <option>Cuti Kemalangan</option>
                                        <option>Cuti Ibadah</option>
                                    </optgroup>
                                </select>
                                <div id="annual-leave-note" class="note-red hidden">
                                    *Cuti direncanakan minimal 1 Bulan
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                        <div class="form-group">
                            <label for="start-date">Tanggal Mulai</label>
                            <input type="date" id="start-date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end-date">Tanggal Selesai</label>
                            <input type="date" id="end-date" class="form-control" required>
                        </div>
                        <span id="leave-summary-note" class="note-red" style="font-size: 12px; margin-top: 5px;"></span>
                    </div>
                        <div class="form-group">
                            <label for="reason">Alasan Cuti</label>
                            <textarea id="reason" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="address">Alamat <span class="note-red-inline">* Alamat selama cuti</span></label>
                            <textarea id="address" rows="4" class="form-control" required></textarea>
                        </div>
                        <div id="file-upload-group" class="form-group hidden">
                            <label for="file-attach">File Perizinan Dikirim Melalui Email (admin@gmail.com)<span class="note-red-inline">*Surat bisa menyusul</span></label>
                            <input type="file" id="file-attach" class="file-hidden-input" accept=".pdf, .doc, .docx, .jpg, .jpeg, .png, .heic, .mov, .mp4, .xml">
                            <div class="custom-file-upload">
                                <!-- <label for="file-attach" class="upload-button">
                                    <i class="fas fa-upload"></i> <span class="button-text">Pilih File</span>
                                </label> -->
                                <!-- <span id="file-name-display" class="file-name-display">Belum ada file dipilih</span> -->
                                <a href="https://mail.google.com/mail/u/0/#inbox">Masuk Email</a>
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Ajukan Permohonan</button>
                    </form>
                </div>

                <div id="popup-success" class="popup-modal">
                    <div class="popup-content">
                        <div class="icon-container">
                            <i class="fas fa-check-circle check-icon"></i>
                        </div>
                        <h3>Permohonan Sedang Diproses!</h3>
                        <p>Mohon tunggu persetujuan dari atasan Anda.</p>
                    </div>
                </div>

                <div id="popup-pending" class="popup-modal">
                    <div class="popup-content">
                        <div class="icon-container">
                            <i class="fas fa-exclamation-circle exclamation-icon"></i>
                        </div>
                        <h3>Anda Sedang Mengajukan Permohonan Cuti</h3>
                        <p>Mohon tunggu!</p>
                    </div>
                </div>

                <div class="history-section">
                    <h2>Riwayat Cuti</h2>

                    <h3>Cuti Tahunan</h3>

                <div class="leave-table-container">
                    <table class="history-table annual-leave-table">
                        <thead>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Durasi (Hari)</th>
                                <th>Status</th>
                                <th>Surat Persetujuan</th>
                            </tr>
                        </thead>
                        <tbody id="annual-leave-body"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Sisa Cuti Tahunan:</strong></td>
                                <td id="annual-leave-remaining">12 Hari</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <h3>Cuti Lainnya</h3>

                <div class="leave-table-container">
                    <table class="history-table other-leave-table">
                        <thead>
                            <tr>
                                <th>Jenis Cuti</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th>Surat Persetujuan</th>
                            </tr>
                        </thead>
                        <tbody id="other-leave-body"></tbody>
                    </table>
                </div>
                    </div>
            </div>
        </div>
    </main>

        <div id="loading-overlay" class="loading-overlay hidden">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>Harap Ditunggu...</p>
        </div>
    </div>
    
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
