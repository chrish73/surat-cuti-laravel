document.addEventListener('DOMContentLoaded', async () => {
    const token = sessionStorage.getItem('api_token');
    if (!token) {
        window.location.href = '/'; // Redirect jika tidak ada token
        return;
    }

    // Ambil data karyawan dan riwayat cuti dari API
    const fetchEmployeeData = async () => {
        const response = await fetch('/api/karyawan/info', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        if (response.ok) {
            document.getElementById('employee-name').textContent = data.nama;
            document.getElementById('employee-id').textContent = data.id_karyawan;
            document.getElementById('employee-unit').textContent = data.unit;
            document.getElementById('annual-leave-remaining').textContent = `${data.jatah_cuti_tahunan} Hari`;
        }
    };

    const fetchLeaveHistory = async () => {
        const response = await fetch('/api/permohonan/history', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const leaveHistory = await response.json();
        updateLeaveHistoryTables(leaveHistory);
    };

    const updateLeaveHistoryTables = (leaveHistory) => {
        const annualLeaveBody = document.getElementById('annual-leave-body');
        const otherLeaveBody = document.getElementById('other-leave-body');
        annualLeaveBody.innerHTML = '';
        otherLeaveBody.innerHTML = '';

        leaveHistory.forEach(leave => {
            const row = document.createElement('tr');
            if (leave.jenis_cuti === 'Cuti Tahunan') {
                row.innerHTML = `
                    <td>${leave.created_at.substring(0, 10)}</td>
                    <td>${leave.tanggal_mulai}</td>
                    <td>${leave.tanggal_selesai}</td>
                    <td>${leave.durasi} Hari</td>
                    <td><span class="status ${leave.status.toLowerCase()}">${leave.status}</span></td>
                `;
                annualLeaveBody.appendChild(row);
            } else {
                row.innerHTML = `
                    <td>${leave.jenis_cuti}</td>
                    <td>${leave.tanggal_mulai}</td>
                    <td>${leave.tanggal_selesai}</td>
                    <td><span class="status ${leave.status.toLowerCase()}">${leave.status}</span></td>
                `;
                otherLeaveBody.appendChild(row);
            }
        });
    };

    // (LANJUTAN LOGIKA VALIDASI DARI KODE ASLI) ...
    // ...
    // Mengganti tombol submit dengan Fetch API
    const submitButton = document.querySelector('.submit-btn');
    submitButton.addEventListener('click', async (e) => {
        e.preventDefault();
        // (Logika validasi form dari kode asli tetap sama)
        const formData = {
            jenis_cuti: document.getElementById('leave-type').value,
            tanggal_mulai: document.getElementById('start-date').value,
            tanggal_selesai: document.getElementById('end-date').value,
            alasan: document.getElementById('reason').value,
            alamat: document.getElementById('address').value
        };

        const response = await fetch('/api/permohonan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();
        if (response.ok) {
            // Tampilkan pop-up sukses
            // Muat ulang riwayat cuti
            fetchLeaveHistory();
        } else {
            alert('Gagal mengajukan permohonan: ' + result.message);
        }
    });

    // Panggil fungsi-fungsi ini saat halaman dimuat
    await fetchEmployeeData();
    await fetchLeaveHistory();
});
