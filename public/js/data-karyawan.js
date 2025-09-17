document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('api_token');
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }

    const karyawanList = document.getElementById('karyawan-list');
    const form = document.getElementById('karyawan-form');
    const formTitle = document.getElementById('form-title');
    const karyawanIdInput = document.getElementById('karyawan-id');
    const namaInput = document.getElementById('nama');
    const idKaryawanInput = document.getElementById('id_karyawan');
    const emailInput = document.getElementById('email');
    const unitInput = document.getElementById('unit');
    const jatahCutiInput = document.getElementById('jatah_cuti_tahunan');
    const isAdminCheckbox = document.getElementById('is_admin');
    const passwordGroup = document.getElementById('password-group');
    const passwordInput = document.getElementById('password');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const deleteModal = document.getElementById('delete-modal');
    const closeDeleteModal = document.getElementById('close-delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');

    let currentKaryawanIdToDelete = null;

    // Tampilkan/sembunyikan input password
    isAdminCheckbox.addEventListener('change', () => {
        passwordGroup.style.display = isAdminCheckbox.checked ? 'block' : 'none';
        passwordInput.required = isAdminCheckbox.checked;
    });

    const loadKaryawan = async () => {
        karyawanList.innerHTML = '<tr><td colspan="7">Memuat data...</td></tr>';
        try {
            const response = await fetch('/api/admin/karyawan', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (!response.ok) {
                throw new Error('Gagal memuat data karyawan.');
            }
            const karyawan = await response.json();
            renderKaryawanTable(karyawan);
        } catch (error) {
            console.error('Error:', error);
            karyawanList.innerHTML = '<tr><td colspan="7">Gagal memuat data.</td></tr>';
        }
    };

    const renderKaryawanTable = (karyawan) => {
        karyawanList.innerHTML = '';
        if (karyawan.length === 0) {
            karyawanList.innerHTML = '<tr><td colspan="7" style="text-align:center;">Tidak ada data karyawan.</td></tr>';
            return;
        }

        karyawan.forEach(k => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${k.id_karyawan}</td>
                <td>${k.nama}</td>
                <td>${k.unit}</td>
                <td>${k.email}</td>
                <td>${k.jatah_cuti_tahunan} Hari</td>
                <td>${k.is_admin ? 'Ya' : 'Tidak'}</td>
                <td>
                    <button class="action-btn edit-btn" data-id="${k.id}">Edit</button>
                    <button class="action-btn delete-btn" data-id="${k.id}">Hapus</button>
                </td>
            `;
            karyawanList.appendChild(row);
        });

        document.querySelectorAll('.edit-btn').forEach(btn => btn.addEventListener('click', handleEdit));
        document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', handleDelete));
    };

    const handleEdit = async (e) => {
        const id = e.target.dataset.id;
        try {
            const response = await fetch(`/api/admin/karyawan/${id}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (!response.ok) {
                throw new Error('Gagal mengambil data karyawan.');
            }
            const k = await response.json();
            formTitle.textContent = 'Edit Data Karyawan';
            karyawanIdInput.value = k.id;
            namaInput.value = k.nama;
            idKaryawanInput.value = k.id_karyawan;
            emailInput.value = k.email;
            unitInput.value = k.unit;
            jatahCutiInput.value = k.jatah_cuti_tahunan;
            isAdminCheckbox.checked = k.is_admin;
            passwordGroup.style.display = k.is_admin ? 'block' : 'none';
            passwordInput.required = false; // Password is not required for editing
            cancelEditBtn.style.display = 'inline-block';
        } catch (error) {
            alert('Terjadi kesalahan saat mengedit data.');
            console.error('Error:', error);
        }
    };

    const handleDelete = (e) => {
        currentKaryawanIdToDelete = e.target.dataset.id;
        deleteModal.style.display = 'flex';
    };

    const resetForm = () => {
        form.reset();
        formTitle.textContent = 'Tambah Karyawan Baru';
        karyawanIdInput.value = '';
        passwordGroup.style.display = 'none';
        passwordInput.required = false;
        cancelEditBtn.style.display = 'none';
    };

    form.addEventListener('submit', async (e) => {
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
            is_admin: isAdminCheckbox.checked,
            password: passwordInput.value,
        };

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (response.ok) {
                alert(result.message);
                resetForm();
                loadKaryawan();
            } else {
                alert('Gagal menyimpan data: ' + (result.message || 'Terjadi kesalahan.'));
            }
        } catch (error) {
            alert('Terjadi kesalahan pada server.');
            console.error('Error:', error);
        }
    });

    cancelEditBtn.addEventListener('click', resetForm);

    closeDeleteModal.addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });

    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.style.display = 'none';
    });

    confirmDeleteBtn.addEventListener('click', async () => {
        try {
            const response = await fetch(`/api/admin/karyawan/${currentKaryawanIdToDelete}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const result = await response.json();
            if (response.ok) {
                alert(result.message);
                loadKaryawan();
            } else {
                alert('Gagal menghapus data: ' + (result.message || 'Terjadi kesalahan.'));
            }
        } catch (error) {
            alert('Terjadi kesalahan pada server.');
            console.error('Error:', error);
        } finally {
            deleteModal.style.display = 'none';
        }
    });

    loadKaryawan();
});
