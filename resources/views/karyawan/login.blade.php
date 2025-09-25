<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Permohonan Cuti</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="main-card-container">
        <div class="left-panel">
            <div class="card-logo-container-login">
                <img src="{{ asset('images/logo.png') }}" alt="Infranexia by Telkom Indonesia Logo">
            </div>
            <div class="form-section">
                <h2>ROC TIF-1</h2>
                <h2>Surat Permohonan Cuti</h2>
                <form id="info-form">
                    <div class="login-form-group">
                        <label for="name">Nama</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="login-form-group">
                        <label for="id">Perner</label>
                        <input type="text" id="id" class="form-control" required>
                    </div>
                    <div class="login-form-group">
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
                    <div class="login-form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <button type="submit" class="submit-btn">Masuk</button>
                </form>
            </div>
        </div>
        <div class="right-panel">
            <img src="{{ asset('images/gedung.png') }}" alt="Gambar Latar Belakang" class="right-image">
        </div>
    </div>
    <script src="{{ asset('js/halaman.js') }}"></script>
</body>
</html>