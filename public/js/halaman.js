//halaman.js (untuk login dari karyawan)

document.addEventListener('DOMContentLoaded', () => {
    const infoForm = document.getElementById('info-form');
    infoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const id_karyawan = document.getElementById('id').value; // Mengambil ID Karyawan

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                // Kirim ID Karyawan dan email
                body: JSON.stringify({ id_karyawan: id_karyawan, email: email })
            });

            const data = await response.json();
            if (response.ok) {
                sessionStorage.setItem('api_token', data.api_token);
                sessionStorage.setItem('employeeName', data.karyawan.nama);
                sessionStorage.setItem('employeeId', data.karyawan.id_karyawan);
                sessionStorage.setItem('employeeUnit', data.karyawan.unit);
                sessionStorage.setItem('employeeEmail', data.karyawan.email); // Simpan email yang sudah diperbarui

                window.location.href = '/dashboard';
            } else {
                alert('Login Gagal: ' + data.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat login.');
        }
    });
});
