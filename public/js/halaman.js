//halaman.js (untuk login dari karyawan)

document.addEventListener('DOMContentLoaded', () => {
    const infoForm = document.getElementById('info-form');
    infoForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            });

            const data = await response.json();
            if (response.ok) {
                // Simpan token dan info karyawan ke sessionStorage
                sessionStorage.setItem('api_token', data.api_token);
                sessionStorage.setItem('employeeName', data.karyawan.nama);
                sessionStorage.setItem('employeeId', data.karyawan.id_karyawan);
                sessionStorage.setItem('employeeUnit', data.karyawan.unit);

                window.location.href = '/dashboard';
            } else {
                alert('Login Gagal: ' + data.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat login.');
        }
    });
});
