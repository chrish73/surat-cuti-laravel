document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('api_token');

    if (!token) {
        window.location.href = '/admin/login';
        return;
    }

    const manajerForm = document.getElementById('manajerForm');
    const manajerIdInput = document.getElementById('manajerId');
    const manajerTableBody = document.getElementById('manajerTableBody');
    const unitContainer = document.getElementById('unit-container');
    const addUnitBtn = document.getElementById('addUnitBtn');
    const resetBtn = document.getElementById('resetBtn');

    let availableUnits = [];

    const fetchUniqueUnits = async () => {
        try {
            const response = await fetch('/api/admin/units', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (!response.ok) {
                throw new Error('Gagal mengambil daftar unit.');
            }
            availableUnits = await response.json();
            
            // Inisialisasi: Buat setidaknya satu dropdown unit saat pertama kali memuat
            if (unitContainer.children.length === 0) {
                 createUnitDropdown(); 
            }
            fetchManajer();
        } catch (error) {
            console.error('Error fetching unique units:', error);
        }
    };

    const createUnitDropdown = (selectedUnit = '') => {
        const div = document.createElement('div');
        div.classList.add('unit-group');

        const select = document.createElement('select');
        select.name = 'units[]';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Pilih Unit';
        select.appendChild(defaultOption);

        availableUnits.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit;
            option.textContent = unit;
            if (unit.trim() === selectedUnit.trim()) { 
                option.selected = true;
            }
            select.appendChild(option);
        });
        div.appendChild(select);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '❌ Hapus';
        removeBtn.addEventListener('click', () => {
            div.remove();
            // Pastikan setidaknya ada satu dropdown unit jika semua dihapus
            if (unitContainer.children.length === 0) {
                createUnitDropdown();
            }
        });

        div.appendChild(removeBtn);
        unitContainer.appendChild(div);
    };

    const fetchManajer = async () => {
        manajerTableBody.innerHTML = '<tr><td colspan="6" data-label="Status">Memuat data...</td></tr>';
        try {
            const response = await fetch('/api/admin/manajer', {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.indexOf('text/html') !== -1) {
                sessionStorage.removeItem('api_token');
                window.location.href = '/admin/login';
                return;
            }

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Gagal memuat data manajer.');
            }

            const data = await response.json();
            renderManajerTable(data.manajer);
        } catch (error) {
            console.error('Error fetching manajer:', error);
            manajerTableBody.innerHTML = '<tr><td colspan="6" data-label="Status">Gagal memuat data. Periksa token Anda.</td></tr>';
        }
    };

    const renderManajerTable = (manajer) => {
        manajerTableBody.innerHTML = '';
        if (manajer.length === 0) {
            manajerTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;" data-label="Status">Tidak ada data manajer.</td></tr>';
            return;
        }

        manajer.forEach(m => {
            const unitsList = m.units && m.units.length > 0 ? m.units.join(', ') : '-';
            const row = manajerTableBody.insertRow();
            row.innerHTML = `
                <td data-label="ID">${m.id}</td>
                <td data-label="Nama Manajer">${m.nama_manajer}</td>
                <td data-label="ID Manajer">${m.id_manajer}</td>
                <td data-label="Jabatan">${m.jabatan_manajer}</td>
                <td data-label="Unit">${unitsList}</td>
                <td data-label="Aksi">
                    <button class="btn-edit" data-id="${m.id}" data-nama="${m.nama_manajer}" data-id-manajer="${m.id_manajer}" data-jabatan="${m.jabatan_manajer}" data-units="${unitsList}">✏️ Edit</button>
                    <button class="btn-hapus" data-id="${m.id}">🗑️ Hapus</button>
                </td>
            `;
        });

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', (e) => {
                const { id, nama, idManajer, jabatan, units } = e.target.dataset;
                editManajer(id, nama, idManajer, jabatan, units);
                // Scroll ke atas formulir setelah mengedit (UX responsif)
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        document.querySelectorAll('.btn-hapus').forEach(button => {
            button.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                deleteManajer(id);
            });
        });
    };

    const resetForm = () => {
        manajerForm.reset();
        manajerIdInput.value = '';
        unitContainer.innerHTML = '';
        // Selalu pastikan setidaknya satu dropdown ada setelah reset
        createUnitDropdown();
    };

    const editManajer = (id, nama, idManajer, jabatan, units) => {
        manajerIdInput.value = id;
        document.getElementById('nama_manajer').value = nama;
        document.getElementById('id_manajer').value = idManajer;
        document.getElementById('jabatan_manajer').value = jabatan;

        unitContainer.innerHTML = '';
        
        const unitsArray = units.split(',').map(u => u.trim()).filter(u => u !== '' && u !== '-');
        
        if (unitsArray.length > 0) {
            unitsArray.forEach(unit => {
                createUnitDropdown(unit);
            });
        } else {
            // Jika tidak ada unit terlampir, tampilkan satu dropdown kosong
            createUnitDropdown();
        }
    };

    const deleteManajer = async (id) => {
        if (confirm('Apakah Anda yakin ingin menghapus manajer ini?')) {
            try {
                const response = await fetch(`/api/admin/manajer/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const result = await response.json();
                if (response.ok) {
                    alert(result.message);
                    fetchManajer();
                } else {
                    const errorMessage = result.message || JSON.stringify(result) || 'Terjadi kesalahan.';
                    alert('Gagal menghapus data: ' + errorMessage);
                }
            } catch (error) {
                alert('Terjadi kesalahan pada server.');
                console.error('Error:', error);
            }
        }
    };

    manajerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = manajerIdInput.value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/api/admin/manajer/${id}` : '/api/admin/manajer';

        const nama_manajer = document.getElementById('nama_manajer').value;
        const id_manajer = document.getElementById('id_manajer').value;
        const jabatan_manajer = document.getElementById('jabatan_manajer').value;

        // Ambil semua unit yang terpilih dan filter yang kosong
        const units = Array.from(unitContainer.querySelectorAll('select'))
            .map(select => select.value.trim())
            .filter(value => value !== '');

        const data = { nama_manajer, id_manajer, jabatan_manajer, units };

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (response.ok) {
                alert(result.message);
                resetForm();
                fetchManajer();
            } else {
                const errorMessage = result.message || JSON.stringify(result) || 'Terjadi kesalahan.';
                alert('Gagal menyimpan data: ' + errorMessage);
            }
        } catch (error) {
            alert('Terjadi kesalahan pada server.');
            console.error('Error:', error);
        }
    });

    addUnitBtn.addEventListener('click', () => {
        createUnitDropdown();
    });

    resetBtn.addEventListener('click', resetForm);

    // Initial load
    fetchUniqueUnits();
});