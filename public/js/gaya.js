// gaya.js

document.addEventListener('DOMContentLoaded', () => {
    const isAdmin = sessionStorage.getItem('is_admin');
    const token = sessionStorage.getItem('api_token');
    const unitFilterSelect = document.getElementById('unit-filter');
    const searchNameInput = document.getElementById('search-name');
    const startDateFilterInput = document.getElementById('start-date-filter');
    const endDateFilterInput = document.getElementById('end-date-filter');
    const exportButton = document.getElementById('export-excel-btn');
    const rejectModal = document.getElementById('reject-modal');
    const closeRejectModal = document.getElementById('close-reject-modal');
    const confirmRejectBtn = document.getElementById('confirm-reject-btn');
    const rejectionReasonTextarea = document.getElementById('rejection-reason');
    const buttonText = document.getElementById('button-text');
    const loadingSpinner = document.getElementById('loading-spinner');
    // Ambil elemen overlay loading
    const loadingOverlay = document.getElementById('loading-overlay');
    let currentPermohonanId = null;

    if (!token || isAdmin !== 'true') {
        window.location.href = '/admin/login';
        return;
    }

    // Fungsi untuk mengontrol loading overlay global
    const showGlobalLoading = (show) => {
        if (show) {
            loadingOverlay.classList.add('visible');
        } else {
            loadingOverlay.classList.remove('visible');
        }
    };

    // Fungsi untuk mengontrol loading pada tombol tolak
    const showRejectButtonLoading = (isLoading) => {
        if (isLoading) {
            confirmRejectBtn.disabled = true;
            buttonText.textContent = 'Memproses...';
            loadingSpinner.classList.remove('hidden');
        } else {
            confirmRejectBtn.disabled = false;
            buttonText.textContent = 'Kirim Penolakan';
            loadingSpinner.classList.add('hidden');
        }
    };

    const showNotificationPopup = (type, message) => {
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

    const changeStatus = async (id, newStatus, alasanPenolakan = null) => {
        showGlobalLoading(true); // Tampilkan loading global
        try {
            const response = await fetch('/api/admin/change-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ id: id, status: newStatus, alasan_penolakan: alasanPenolakan })
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
        } finally {
            showGlobalLoading(false); // Sembunyikan loading global setelah proses selesai
        }
    };

    window.showInfo = (alasan, alamat) => {
        const popupOverlay = document.getElementById('notification-popup');
        const popupIcon = document.getElementById('popup-icon');
        const popupTitle = document.getElementById('popup-title');
        const popupMessage = document.getElementById('popup-message');
        const popupCloseBtn = document.getElementById('popup-close-btn');

        popupIcon.className = 'popup-icon info';
        popupIcon.innerHTML = '';
        popupTitle.textContent = 'Detail Permohonan Cuti';
        popupMessage.innerHTML = `<strong>Alasan Cuti:</strong><br>${alasan}<br><br><strong>Alamat Selama Cuti:</strong><br>${alamat}`;
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

    const addEventListeners = () => {
        document.querySelectorAll('.approve-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                await changeStatus(id, 'Disetujui');
            });
        });

        document.querySelectorAll('.reject-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                currentPermohonanId = e.target.dataset.id;
                rejectModal.classList.add('active');
            });
        });

        document.querySelectorAll('.revert-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                await changeStatus(id, 'Menunggu');
            });
        });
    };

    const loadRequests = async (unitFilter = '', nameFilter = '', startDateFilter = '', endDateFilter = '') => {
        const tableBody = document.getElementById('request-list');
        tableBody.innerHTML = '<tr><td colspan="11">Memuat data...</td></tr>';

        try {
            const params = new URLSearchParams();
            if (unitFilter) {
                params.append('unit', unitFilter);
            }
            if (nameFilter) {
                params.append('name', nameFilter);
            }
            if (startDateFilter) {
                params.append('start_date', startDateFilter);
            }
            if (endDateFilter) {
                params.append('end_date', endDateFilter);
            }
            
            const apiUrl = `/api/admin/permohonan?${params.toString()}`;
            
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
                    ? `<a href="/storage/${req.lampiran_file}" target="_blank" class="action-btn">Lihat</a>`
                    : `<span class="tindakan-selesai">Tidak ada</span>`;

                let actionButtons;
                if (req.status === 'Menunggu') {
                    actionButtons = `<div class="action-buttons-group">
                                        <button class="approve-btn action-icon-btn approve" data-id="${req.id}" title="Setujui"></button>
                                        <button class="reject-btn action-icon-btn reject" data-id="${req.id}" title="Tolak"></button>
                                    </div>`;
                } else {
                    actionButtons = `<div class="action-buttons-group">
                                        <button class="revert-btn action-btn" data-id="${req.id}">Edit Status</button>
                                    </div>`;
                }

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
                `;
                tableBody.appendChild(row);
            });

            addEventListeners();
        } catch (error) {
            tableBody.innerHTML = '<tr><td colspan="11">Gagal memuat data. Mohon cek koneksi Anda.</td></tr>';
            console.error('Error:', error);
        }
    };
    
    // Function to handle all filter changes
    const handleFilterChange = () => {
        const unit = unitFilterSelect.value;
        const name = searchNameInput.value.trim();
        const startDate = startDateFilterInput.value;
        const endDate = endDateFilterInput.value; // Dapatkan nilai tanggal selesai
        loadRequests(unit, name, startDate, endDate);
    };

    if (unitFilterSelect) {
        unitFilterSelect.addEventListener('change', handleFilterChange);
    }
    
    if (searchNameInput) {
        let searchTimeout;
        searchNameInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                handleFilterChange();
            }, 500); 
        });
    }

    if (startDateFilterInput) {
        startDateFilterInput.addEventListener('change', handleFilterChange);
    }

    // Tambahkan event listener untuk input tanggal selesai
    if (endDateFilterInput) {
        endDateFilterInput.addEventListener('change', handleFilterChange);
    }

    if (exportButton) {
        exportButton.addEventListener('click', async () => {
            const unitFilter = unitFilterSelect.value;
            const nameFilter = searchNameInput.value.trim();
            const startDateFilter = startDateFilterInput.value;
            const endDateFilter = endDateFilterInput.value;

            // Buat URL dengan parameter filter yang ada
            const params = new URLSearchParams();
            if (unitFilter) {
                params.append('unit', unitFilter);
            }
            if (nameFilter) {
                params.append('name', nameFilter);
            }
            if (startDateFilter) {
                params.append('start_date', startDateFilter);
            }
            if (endDateFilter) {
                params.append('end_date', endDateFilter);
            }
            
            const exportUrl = `/api/admin/export-permohonan?${params.toString()}`;

            showGlobalLoading(true); // Tampilkan loading global untuk export

            try {
                const response = await fetch(exportUrl, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentDisposition = response.headers.get('Content-Disposition');
                const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                const fileName = fileNameMatch ? fileNameMatch[1] : 'riwayat_cuti.csv';

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                showNotificationPopup('success', 'File berhasil diunduh!');
            } catch (error) {
                console.error('Error during export:', error);
                showNotificationPopup('error', 'Gagal mengunduh file. Mohon coba lagi.');
            } finally {
                showGlobalLoading(false); // Sembunyikan loading global setelah proses selesai
            }
        });
    }

    closeRejectModal.addEventListener('click', () => {
        rejectModal.classList.remove('active');
        rejectionReasonTextarea.value = '';
    });

    confirmRejectBtn.addEventListener('click', async () => {
        const reason = rejectionReasonTextarea.value;
        if (!reason.trim()) {
            alert('Alasan penolakan tidak boleh kosong.');
            return;
        }

        showRejectButtonLoading(true);
        try {
            await changeStatus(currentPermohonanId, 'Ditolak', reason);
        } finally {
            showRejectButtonLoading(false);
            rejectModal.classList.remove('active');
            rejectionReasonTextarea.value = '';
        }
    });

    window.addEventListener('click', (event) => {
        if (event.target === rejectModal) {
            rejectModal.classList.remove('active');
            rejectionReasonTextarea.value = '';
        }
    });

    loadRequests();
});