<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Manajer</title>
    <link rel="stylesheet" href="{{ asset('css/matel.css') }}"> 
</head>
<body>
    <div class="container">

        
        <div class="card">
                   <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="card-logo">
             <h1>Manajemen Manajer</h1>

            <form id="manajerForm">
                <input type="hidden" id="manajerId">
                
                <div class="form-row">
                    <div>
                        <label for="nama_manajer">Nama Manajer:</label>
                        <input type="text" id="nama_manajer" required>
                    </div>
                    <div>
                        <label for="id_manajer">ID Manajer:</label>
                        <input type="text" id="id_manajer" required>
                    </div>
                    <div>
                        <label for="jabatan_manajer">Jabatan:</label>
                        <input type="text" id="jabatan_manajer" required>
                    </div>
                </div>

                <label style="margin-top: 15px;">Unit yang Dibawahi:</label>
                <div id="unit-container">
                    </div>
                <button type="button" id="addUnitBtn">➕ Tambah Unit Lain</button>
                
                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

                <div class="form-actions">
                    <button type="submit">💾 Simpan Manajer</button>
                    <button type="button" id="resetBtn">🔄 Reset Form</button>
                </div>
            </form>
        </div>

       
        <div class="card">
             <h2>Daftar Manajer</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Manajer</th>
                        <th>ID Manajer</th>
                        <th>Jabatan</th>
                        <th>Unit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="manajerTableBody">
                    </tbody>
            </table>
        </div>
    </div>

    <script src="{{ asset('js/data-manajer.js') }}"></script>
</body>
</html>