document.addEventListener('DOMContentLoaded', async () => {
    // Ambil token dari sessionStorage
    const apiToken = sessionStorage.getItem('api_token');
    if (!apiToken) {
        window.location.href = '/admin/login';
        return;
    }

    const rejectModal = document.getElementById('reject-modal');
    const closeBtn = document.getElementById('close-reject-modal');
    const confirmRejectBtn = document.getElementById('confirm-reject-btn');
    const rejectionReasonTextarea = document.getElementById('rejection-reason');
    let currentPermohonanId = null;

    const showNotification = (title, message, isSuccess) => {
        const popup = document.getElementById('notification-popup');
        const icon = document.getElementById('popup-icon');
        const titleEl = document.getElementById('popup-title');
        const messageEl = document.getElementById('popup-message');

        titleEl.textContent = title;
        messageEl.textContent = message;

        if (isSuccess) {
            icon.innerHTML = '<i class="fas fa-check-circle"></i>';
            icon.style.color = 'green';
        } else {
            icon.innerHTML = '<i class="fas fa-times-circle"></i>';
            icon.style.color = 'red';
        }
        popup.style.display = 'flex';
    };

    const fetchLeaveRequests = async () => {
        const unitFilter = document.getElementById('unit-filter').value;
        const url = unitFilter ? `/api/admin/permohonan?unit=${unitFilter}` : '/api/admin/permohonan';
        try {
            const response = await fetch(url, {
                headers: { 'Authorization': `Bearer ${apiToken}` }
            });
            const data = await response.json();
            if (response.ok) {
                renderTable(data);
                addEventListeners();
            } else {
                console.error('Failed to fetch data:', data.message);
                showNotification('Error', 'Gagal memuat data permohonan.', false);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error', 'Terjadi kesalahan saat memuat data.', false);
        }
    };

    const renderTable = (requests) => {
        const tableBody = document.getElementById('request-list');
        tableBody.innerHTML = '';
        requests.forEach(req => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${req.karyawan.nama}</td>
                <td>${req.karyawan.id_karyawan}</td>
                <td>${req.karyawan.unit}</td>
                <td>${req.jenis_cuti}</td>
                <td>${req.karyawan.jatah_cuti_tahunan} Hari</td>
                <td>${req.tanggal_mulai} sampai ${req.tanggal_selesai}</td>
                <td>${req.durasi} Hari</td>
                <td>
                    <strong>Alasan:</strong> ${req.alasan}
                    <br>
                    <strong>Alamat:</strong> ${req.alamat_selama_cuti}
                </td>
                <td><span class="status status-${req.status.toLowerCase()}">${req.status}</span></td>
                <td>
                    ${req.file_lampiran ? `<a href="/storage/${req.file_lampiran}" target="_blank" class="action-btn">Lihat</a>` : 'Tidak ada'}
                </td>
                <td class="action-buttons">
                    ${req.status === 'Menunggu' ? `
                    <button class="approve-btn action-btn" data-id="${req.id}">Setujui</button>
                    <button class="reject-btn action-btn" data-id="${req.id}">Tolak</button>
                    ` : `
                    <button class="action-btn-disabled" disabled>Setujui</button>
                    <button class="action-btn-disabled" disabled>Tolak</button>
                    `}
                </td>
                <td>
                    <button class="edit-btn action-btn" data-id="${req.id}">Edit</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    };

    const addEventListeners = () => {
        document.querySelectorAll('.approve-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                await updateLeaveRequestStatus(id, 'approve');
            });
        });

        document.querySelectorAll('.reject-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                currentPermohonanId = e.target.dataset.id;
                rejectModal.style.display = 'flex';
            });
        });
    };

    const updateLeaveRequestStatus = async (id, status) => {
        try {
            const response = await fetch(`/api/admin/${status}/${id}`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${apiToken}` }
            });
            const result = await response.json();
            if (response.ok) {
                showNotification('Berhasil!', result.message, true);
                fetchLeaveRequests();
            } else {
                showNotification('Gagal!', result.message, false);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Gagal!', 'Terjadi kesalahan.', false);
        }
    };

    document.getElementById('export-excel-btn').addEventListener('click', () => {
        const unit = document.getElementById('unit-filter').value;
        window.location.href = `/api/admin/export-excel?unit=${unit}`;
    });

    document.getElementById('unit-filter').addEventListener('change', fetchLeaveRequests);

    // Event listener untuk modal penolakan
    closeBtn.addEventListener('click', () => {
        rejectModal.style.display = 'none';
        rejectionReasonTextarea.value = '';
    });

    confirmRejectBtn.addEventListener('click', async () => {
        const reason = rejectionReasonTextarea.value;
        if (!reason.trim()) {
            alert('Alasan penolakan tidak boleh kosong.');
            return;
        }

        try {
            const response = await fetch(`/api/admin/reject/${currentPermohonanId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiToken}`
                },
                body: JSON.stringify({ alasan_penolakan: reason })
            });

            const result = await response.json();
            if (response.ok) {
                showNotification('Berhasil!', 'Permohonan berhasil ditolak.', true);
                fetchLeaveRequests();
            } else {
                showNotification('Gagal!', 'Gagal menolak permohonan: ' + result.message, false);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Gagal!', 'Terjadi kesalahan saat menolak permohonan.', false);
        }

        rejectModal.style.display = 'none';
        rejectionReasonTextarea.value = '';
    });

    fetchLeaveRequests();
});
