// gaya.js

// Fungsi untuk memuat data permohonan dari API
const loadRequests = async (unitFilter = '') => {
    const tableBody = document.getElementById('request-list');
    tableBody.innerHTML = '<tr><td colspan="11">Memuat data...</td></tr>';

    try {
        const token = sessionStorage.getItem('api_token');
        let apiUrl = '/api/admin/permohonan';
        if (unitFilter) {
            apiUrl += `?unit=${unitFilter}`;
        }

        const response = await fetch(apiUrl, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const requests = await response.json();
        tableBody.innerHTML = '';

        if (requests.length === 0) {
             tableBody.innerHTML = '<tr><td colspan="11" style="text-align:center;">Tidak ada permohonan cuti.</td></tr>';
             return;
        }

        requests.forEach(req => {
            const row = document.createElement('tr');
            const statusClass = req.status.toLowerCase();
            const fileAttachment = req.lampiran_file
                ? `<button class="action-icon-btn file" onclick="viewFile('${req.lampiran_file}')" title="Lihat File"></button>`
                : `<span class="tindakan-selesai">Tidak ada</span>`;

            const actionButtons = req.status === 'Menunggu'
                ? `<div class="action-buttons-group">
                      <button class="action-icon-btn approve" onclick="changeStatus('${req.id}', 'Disetujui')" title="Setujui"></button>
                      <button class="action-icon-btn reject" onclick="changeStatus('${req.id}', 'Ditolak')" title="Tolak"></button>
                   </div>`
                : `<span class="tindakan-selesai">${req.status}</span>`;

            const editButton = req.status !== 'Menunggu'
                ? `<button class="action-icon-btn edit" onclick="revertStatus('${req.id}')" title="Edit"></button>`
                : '';

            const infoButton = `<button class="action-icon-btn info" onclick="showInfo('${req.alasan}', '${req.alamat_selama_cuti}')" title="Lihat Detail"></button>`;

            row.innerHTML = `
                <td>${req.karyawan.nama}</td>
                <td>${req.karyawan.id_karyawan}</td>
                <td>${req.karyawan.unit}</td>
                <td>${req.jenis_cuti}</td>
                <td>${req.karyawan.jatah_cuti_tahunan} Hari</td>
                <td>${req.tanggal_mulai}</td>
                <td>${req.durasi} Hari</td>
                <td>${infoButton}</td>
                <td><div class="status-badge ${statusClass}">${req.status}</div></td>
                <td>${fileAttachment}</td>
                <td>${actionButtons}</td>
                <td>${editButton}</td>
            `;
            tableBody.appendChild(row);
        });
    } catch (error) {
        tableBody.innerHTML = '<tr><td colspan="11">Gagal memuat data. Mohon cek koneksi Anda.</td></tr>';
        console.error('Error:', error);
    }
};

// Fungsi untuk mengembalikan status via API
const revertStatus = async (id) => {
    try {
        const token = sessionStorage.getItem('api_token');
        const response = await fetch('/api/admin/revert-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (response.ok) {
            showNotificationPopup('info', result.message || 'Status berhasil dikembalikan!');
            loadRequests();
        } else {
            showNotificationPopup('error', result.message || 'Gagal mengembalikan status.');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotificationPopup('error', 'Terjadi kesalahan pada server.');
    }
};

// Fungsi untuk mengubah status via API
const changeStatus = async (id, newStatus) => {
    try {
        const token = sessionStorage.getItem('api_token');
        const response = await fetch('/api/admin/change-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ id: id, status: newStatus })
        });
        const result = await response.json();

        if (response.ok) {
            showNotificationPopup('success', result.message || 'Status berhasil diubah!');
            loadRequests();
        } else {
            showNotificationPopup('error', result.message || 'Gagal mengubah status.');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotificationPopup('error', 'Terjadi kesalahan pada server.');
    }
};

// Fungsi untuk melihat file
const viewFile = (fileName) => {
    const token = sessionStorage.getItem('api_token');
    const fileUrl = `/api/admin/view-file/${fileName}`;

    // Buka di tab baru
    window.open(fileUrl, '_blank');
};

// Fungsi untuk menampilkan pop-up dengan detail alasan dan alamat
window.showInfo = (alasan, alamat) => {
    const popupOverlay = document.getElementById('notification-popup');
    const popupIcon = document.getElementById('popup-icon');
    const popupTitle = document.getElementById('popup-title');
    const popupMessage = document.getElementById('popup-message');
    const popupCloseBtn = document.getElementById('popup-close-btn');

    // Atur konten pop-up
    popupIcon.className = 'popup-icon info';
    popupIcon.innerHTML = '';
    popupTitle.textContent = 'Detail Permohonan Cuti';
    popupMessage.innerHTML = `<strong>Alasan Cuti:</strong><br>${alasan}<br><br><strong>Alamat Selama Cuti:</strong><br>${alamat}`;
    popupCloseBtn.style.display = 'block';

    // Tampilkan pop-up
    popupOverlay.classList.add('show');

    // Event listener untuk tombol tutup
    popupCloseBtn.onclick = function() {
        popupOverlay.classList.remove('show');
    };

    // Event listener untuk klik di luar pop-up
    popupOverlay.addEventListener('click', function(event) {
        if (event.target === popupOverlay) {
            popupOverlay.classList.remove('show');
        }
    });
};

// Fungsi untuk menampilkan pop-up notifikasi (diperbaiki)
window.showNotificationPopup = (type, message) => {
    const popupOverlay = document.getElementById('notification-popup');
    const popupIcon = document.getElementById('popup-icon');
    const popupTitle = document.getElementById('popup-title');
    const popupMessage = document.getElementById('popup-message');
    const popupCloseBtn = document.getElementById('popup-close-btn');

    popupIcon.className = 'popup-icon';

    if (type === 'success') {
        popupIcon.classList.add('success');
        popupIcon.innerHTML = '';
        popupTitle.textContent = 'Berhasil!';
    } else if (type === 'error') {
        popupIcon.classList.add('error');
        popupIcon.innerHTML = '';
        popupTitle.textContent = 'Ditolak!';
    } else if (type === 'info') {
        popupIcon.classList.add('info');
        popupIcon.innerHTML = '';
        popupTitle.textContent = 'Status Diperbarui';
    }

    popupMessage.innerHTML = message;
    popupCloseBtn.style.display = 'block';
    popupOverlay.classList.add('show');

    popupCloseBtn.onclick = function() {
        popupOverlay.classList.remove('show');
    };

    popupOverlay.addEventListener('click', function(event) {
        if (event.target === popupOverlay) {
            popupOverlay.classList.remove('show');
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const isAdmin = sessionStorage.getItem('is_admin');
    const token = sessionStorage.getItem('api_token');
    const unitFilterSelect = document.getElementById('unit-filter');
    const exportButton = document.getElementById('export-excel-btn');

    if (!token || isAdmin !== 'true') {
        window.location.href = '/admin/login';
        return;
    }

    loadRequests();

    if (unitFilterSelect) {
        unitFilterSelect.addEventListener('change', (event) => {
            loadRequests(event.target.value);
        });
    }

    if (exportButton) {
        exportButton.addEventListener('click', async () => {
            const unitFilter = unitFilterSelect.value;
            const token = sessionStorage.getItem('api_token');
            let exportUrl = `/api/admin/export-permohonan`;
            if (unitFilter) {
                exportUrl += `?unit=${unitFilter}`;
            }

            try {
                const response = await fetch(exportUrl, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Mendapatkan nama file dari header Content-Disposition
                const contentDisposition = response.headers.get('Content-Disposition');
                const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                const fileName = fileNameMatch ? fileNameMatch[1] : 'riwayat_cuti.csv';

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = fileName; // Gunakan nama file yang dinamis dari server

                document.body.appendChild(a);
                a.click();

                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                showNotificationPopup('success', 'File berhasil diunduh!');
            } catch (error) {
                console.error('Error during export:', error);
                showNotificationPopup('error', 'Gagal mengunduh file. Mohon coba lagi.');
            }
        });
    }
});
