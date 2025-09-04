document.addEventListener('DOMContentLoaded', async () => {
    const token = sessionStorage.getItem('api_token');
    const employeeName = sessionStorage.getItem('employeeName');
    const employeeId = sessionStorage.getItem('employeeId');
    const employeeUnit = sessionStorage.getItem('employeeUnit');

    if (!token || !employeeName || !employeeId || !employeeUnit) {
        window.location.href = '/'; // Redirect jika data tidak lengkap
        return;
    }

    // Tampilkan data dari sessionStorage secara instan
    document.getElementById('employee-name').textContent = employeeName;
    document.getElementById('employee-id').textContent = employeeId;
    document.getElementById('employee-unit').textContent = employeeUnit;

    // Ambil data sisa cuti dan riwayat dari API
    const fetchEmployeeData = async () => {
        try {
            const response = await fetch('/api/karyawan/info', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            if (response.ok) {
                document.getElementById('annual-leave-remaining').textContent = `${data.jatah_cuti_tahunan} Hari`;
            } else {
                console.error('Gagal mengambil data karyawan dari API.');
            }
        } catch (error) {
            console.error('Terjadi kesalahan saat mengambil data karyawan:', error);
        }
    };

    const fetchLeaveHistory = async () => {
        try {
            const response = await fetch('/api/permohonan/history', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const leaveHistory = await response.json();
            updateLeaveHistoryTables(leaveHistory);
        } catch (error) {
            console.error('Terjadi kesalahan saat mengambil riwayat cuti:', error);
        }
    };

    const updateLeaveHistoryTables = (leaveHistory) => {
        const annualLeaveBody = document.getElementById('annual-leave-body');
        const otherLeaveBody = document.getElementById('other-leave-body');
        annualLeaveBody.innerHTML = '';
        otherLeaveBody.innerHTML = '';

        leaveHistory.forEach(leave => {
            const row = document.createElement('tr');
            const formattedDate = leave.created_at.substring(0, 10);
            if (leave.jenis_cuti === 'Cuti Tahunan') {
                row.innerHTML = `
                    <td>${formattedDate}</td>
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

    const submitButton = document.querySelector('.submit-btn');
    submitButton.addEventListener('click', async (e) => {
        e.preventDefault();

        // Pastikan validasi formulir di sini
        const formData = {
            jenis_cuti: document.getElementById('leave-type').value,
            tanggal_mulai: document.getElementById('start-date').value,
            tanggal_selesai: document.getElementById('end-date').value,
            alasan: document.getElementById('reason').value,
            alamat: document.getElementById('address').value
        };

        try {
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
                // Tampilkan pop-up sukses (kode pop-up di HTML tidak perlu diubah)
                console.log('Permohonan berhasil diajukan!');
                fetchLeaveHistory();
                fetchEmployeeData(); // Perbarui sisa cuti
            } else {
                alert('Gagal mengajukan permohonan: ' + result.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat mengajukan permohonan.');
            console.error('Error:', error);
        }
    });

    // Panggil fungsi untuk mengambil data dinamis saat halaman dimuat
    await fetchEmployeeData();
    await fetchLeaveHistory();
});
