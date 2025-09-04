document.addEventListener('DOMContentLoaded', async () => {
    const token = sessionStorage.getItem('api_token');
    const isAdmin = sessionStorage.getItem('is_admin');

    if (!token || isAdmin !== 'true') {
        window.location.href = '/admin/login';
        return;
    }

    // Fungsi untuk memuat data permohonan dari API
    const loadRequests = async () => {
        const tableBody = document.getElementById('request-list');
        tableBody.innerHTML = '<tr><td colspan="10">Memuat data...</td></tr>';

        try {
            const response = await fetch('/api/admin/permohonan', {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            const requests = await response.json();
            tableBody.innerHTML = ''; // Kosongkan tabel

            requests.forEach(req => {
                const row = document.createElement('tr');
                const statusClass = req.status.toLowerCase();
                const fileAttachment = req.lampiran_file
                    ? `<button class="action-icon-btn file" onclick="viewFile('${req.lampiran_file}')" title="Lihat File">&#128196;</button>`
                    : `<span class="tindakan-selesai">Tidak ada</span>`;

                const actionButtons = req.status === 'Menunggu'
                    ? `<div class="action-buttons-group">
                          <button class="action-icon-btn approve" onclick="changeStatus('${req.id}', 'Disetujui')" title="Setujui">&#10003;</button>
                          <button class="action-icon-btn reject" onclick="changeStatus('${req.id}', 'Ditolak')" title="Tolak">&#10007;</button>
                       </div>`
                    : `<span class="tindakan-selesai">${req.status}</span>`;

                const editButton = req.status !== 'Menunggu'
                    ? `<button class="action-icon-btn edit" onclick="revertStatus('${req.id}')" title="Edit">&#9998;</button>`
                    : '';

                row.innerHTML = `
                    <td>${req.karyawan.nama}</td>
                    <td>${req.karyawan.id_karyawan}</td>
                    <td>${req.jenis_cuti}</td>
                    <td>${req.karyawan.jatah_cuti_tahunan} Hari</td>
                    <td>${req.tanggal_mulai}</td>
                    <td>${req.durasi} Hari</td>
                    <td><div class="status-badge ${statusClass}">${req.status}</div></td>
                    <td>${fileAttachment}</td>
                    <td>${actionButtons}</td>
                    <td>${editButton}</td>
                `;
                tableBody.appendChild(row);
            });

        } catch (error) {
            tableBody.innerHTML = '<tr><td colspan="10">Gagal memuat data.</td></tr>';
            console.error('Error:', error);
        }
    };

    // Fungsi untuk mengubah status via API
    window.changeStatus = async (id, newStatus) => {
        try {
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
                showNotificationPopup('success', result.message);
                loadRequests(); // Muat ulang data
            } else {
                showNotificationPopup('error', result.message);
            }
        } catch (error) {
            showNotificationPopup('error', 'Terjadi kesalahan pada server.');
        }
    };

    // Fungsi untuk mengembalikan status via API
    window.revertStatus = async (id) => {
        try {
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
                showNotificationPopup('info', result.message);
                loadRequests();
            } else {
                showNotificationPopup('error', 'Gagal mengembalikan status.');
            }
        } catch (error) {
            showNotificationPopup('error', 'Terjadi kesalahan pada server.');
        }
    };

    loadRequests();
});
