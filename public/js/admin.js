// public/js/admin.js
document.addEventListener('DOMContentLoaded', () => {
    const isAdmin = sessionStorage.getItem('is_admin');
    const token = sessionStorage.getItem('api_token');
    const karyawanList = document.getElementById('karyawan-list');
    const addKaryawanBtn = document.getElementById('add-karyawan-btn');
    const karyawanModal = document.getElementById('karyawan-modal');
    const closeKaryawanModal = document.getElementById('close-karyawan-modal');
    const karyawanForm = document.getElementById('karyawan-form');
    const modalTitle = document.getElementById('modal-title');
    const submitBtn = document.getElementById('submit-btn');

    const namaInput = document.getElementById('nama');
    const idKaryawanInput = document.getElementById('id_karyawan');
    const emailInput = document.getElementById('email');
    const unitInput = document.getElementById('unit');
    const jatahCutiInput = document.getElementById('jatah_cuti_tahunan');
    const passwordInput = document.getElementById('password');
    const isAdminCheckbox = document.getElementById('is_admin');
    const karyawanIdInput = document.getElementById('karyawan-id');

    if (!token || isAdmin !== 'true') {
        window.location.href = '/admin/login';
        return;
    }

    const showNotificationPopup = (message, isError = false) => {
        const popup = document.getElementById('notification-popup');
        popup.textContent = message;
        popup.style.backgroundColor = isError ? '#dc3545' : '#28a745';
        popup.style.display = 'block';
        setTimeout(() => {
            popup.style.display = 'none';
        }, 3000);
    };

    const loadKaryawan = async () => {
        karyawanList.innerHTML = '<tr><td colspan="6">Memuat data...</td></tr>';
        try {
            const response = await fetch('/api/admin/karyawan', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            karyawanList.innerHTML = '';
            data.forEach(karyawan => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${karyawan.nama}</td>
                    <td>${karyawan.id_karyawan}</td>
                    <td>${karyawan.email}</td>
                    <td>${karyawan.unit}</td>
                    <td>${karyawan.jatah_cuti_tahunan} Hari</td>
                    <td class="action-buttons">
                        <button class="edit-btn" data-id="${karyawan.id}">Edit</button>
                        <button class="delete-btn" data-id="${karyawan.id}">Hapus</button>
                    </td>
                `;
                karyawanList.appendChild(row);
            });
            addEventListeners();
        } catch (error) {
            console.error('Error:', error);
            karyawanList.innerHTML = '<tr><td colspan="6">Gagal memuat data.</td></tr>';
        }
    };

    const addEventListeners = () => {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                modalTitle.textContent = 'Edit Karyawan';
                submitBtn.textContent = 'Update';
                karyawanIdInput.value = id;
                passwordInput.required = false; // Password is not required for update
                await loadKaryawanForEdit(id);
                karyawanModal.style.display = 'flex';
            });
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const id = e.target.dataset.id;
                if (confirm('Apakah Anda yakin ingin menghapus data karyawan ini?')) {
                    await deleteKaryawan(id);
                }
            });
        });
    };

    const loadKaryawanForEdit = async (id) => {
        try {
            const response = await fetch(`/api/admin/karyawan/${id}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const karyawan = await response.json();
            namaInput.value = karyawan.nama;
            idKaryawanInput.value = karyawan.id_karyawan;
            emailInput.value = karyawan.email;
            unitInput.value = karyawan.unit;
            jatahCutiInput.value = karyawan.jatah_cuti_tahunan;
            isAdminCheckbox.checked = karyawan.is_admin;
        } catch (error) {
            console.error('Error:', error);
            showNotificationPopup('Gagal memuat data karyawan untuk edit.', true);
        }
    };

    const deleteKaryawan = async (id) => {
        try {
            const response = await fetch(`/api/admin/karyawan/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const result = await response.json();
            if (response.ok) {
                showNotificationPopup(result.message);
                loadKaryawan();
            } else {
                showNotificationPopup(result.message, true);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotificationPopup('Terjadi kesalahan saat menghapus data.', true);
        }
    };

    addKaryawanBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Tambah Karyawan';
        submitBtn.textContent = 'Simpan';
        karyawanForm.reset();
        karyawanIdInput.value = '';
        passwordInput.required = true;
        karyawanModal.style.display = 'flex';
    });

    closeKaryawanModal.addEventListener('click', () => {
        karyawanModal.style.display = 'none';
    });

    karyawanForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = karyawanIdInput.value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/api/admin/karyawan/${id}` : '/api/admin/karyawan';

        const data = {
            nama: namaInput.value,
            id_karyawan: idKaryawanInput.value,
            email: emailInput.value,
            unit: unitInput.value,
            jatah_cuti_tahunan: jatahCutiInput.value,
            is_admin: isAdminCheckbox.checked
        };
        if (passwordInput.value) {
            data.password = passwordInput.value;
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (response.ok) {
                showNotificationPopup(result.message);
                karyawanModal.style.display = 'none';
                loadKaryawan();
            } else {
                showNotificationPopup(result.message, true);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotificationPopup('Terjadi kesalahan saat menyimpan data.', true);
        }
    });

    // Initial load
    loadKaryawan();
});
