document.addEventListener('DOMContentLoaded', async () => {
    // Ambil data dari sessionStorage
    const name = sessionStorage.getItem('employeeName');
    const id = sessionStorage.getItem('employeeId');
    const unit = sessionStorage.getItem('employeeUnit');
    const email = sessionStorage.getItem('employeeEmail');

    // Tampilkan data yang diterima ke elemen HTML
    if (name) document.getElementById('employee-name').textContent = name;
    if (id) document.getElementById('employee-id').textContent = id;
    if (unit) document.getElementById('employee-unit').textContent = unit;
    // Baris untuk email dihapus karena elemen tidak ada di halaman ini.

    const submitButton = document.querySelector('.submit-btn');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const leaveTypeSelect = document.getElementById('leave-type');
    const reasonTextarea = document.getElementById('reason');
    const addressTextarea = document.getElementById('address');
    const fileAttachInput = document.getElementById('file-attach');
    const annualLeaveNote = document.getElementById('annual-leave-note');
    const popupModalSuccess = document.getElementById('popup-success');
    const popupModalPending = document.getElementById('popup-pending');
    const fileUploadGroup = document.getElementById('file-upload-group');
    const fileNameDisplay = document.getElementById('file-name-display');
    const leaveForm = document.getElementById('leave-form');
    const leaveSummaryNote = document.getElementById('leave-summary-note');
    const loadingOverlay = document.getElementById('loading-overlay'); // Tambahkan ini

    const annualLeaveBody = document.getElementById('annual-leave-body');
    const otherLeaveBody = document.getElementById('other-leave-body');
    const annualLeaveRemaining = document.getElementById('annual-leave-remaining');

    let annualLeaveQuota = 12;
    let leaveHistory = [];
    let hasPendingApplication = false;
    
    // Fungsi untuk mengontrol loading global
    const showGlobalLoading = (show) => {
        if (show) {
            loadingOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('visible');
            if (submitButton) submitButton.disabled = true; // Nonaktifkan tombol saat loading
        } else {
            loadingOverlay.classList.add('hidden');
            loadingOverlay.classList.remove('visible');
            if (submitButton) submitButton.disabled = false; // Aktifkan kembali tombol
        }
    };

    // Fungsi untuk mengunduh PDF secara terprogram
    const downloadSuratPersetujuan = async (permohonanId) => {
        showGlobalLoading(true); // Tampilkan loading
        const token = sessionStorage.getItem('api_token');
        if (!token) {
            alert('Token autentikasi tidak ditemukan. Silakan login kembali.');
            window.location.href = '/';
            showGlobalLoading(false); // Sembunyikan loading jika gagal
            return;
        }

        try {
            const response = await fetch(`/api/permohonan/download-surat/${permohonanId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                alert('Gagal mengunduh file: ' + (errorData.message || 'Terjadi kesalahan.'));
                return;
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Surat_Persetujuan_Cuti_${permohonanId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengunduh surat.');
        } finally {
            showGlobalLoading(false); // Sembunyikan loading setelah selesai
        }
    };

    const fetchEmployeeData = async () => {
        try {
            const token = sessionStorage.getItem("api_token");
            const response = await fetch("/api/karyawan/info", {
                headers: { Authorization: `Bearer ${token}` },
            });
            const data = await response.json();
            if (response.ok) {
                annualLeaveQuota = data.jatah_cuti_tahunan;
                annualLeaveRemaining.textContent = `${annualLeaveQuota} Hari`;
            } else {
                console.error("Failed to fetch employee data:", data.message);
            }
        } catch (error) {
            console.error("Error fetching employee data:", error);
        }
    };

    const fetchLeaveHistory = async () => {
        try {
            const token = sessionStorage.getItem("api_token");
            const response = await fetch("/api/permohonan/history", {
                headers: { Authorization: `Bearer ${token}` },
            });
            const data = await response.json();
            if (response.ok) {
                leaveHistory = data.map((item) => ({
                    id: item.id,
                    leaveType: item.jenis_cuti,
                    startDate: item.tanggal_mulai,
                    endDate: item.tanggal_selesai,
                    days: item.durasi,
                    status: item.status.toLowerCase(),
                    statusText: item.status,
                    submissionDate: new Date(
                        item.created_at
                    ).toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric",
                    }),
                }));
                updateLeaveHistoryTables();
                hasPendingApplication = leaveHistory.some(
                    (leave) => leave.status === "menunggu"
                );
            } else {
                console.error("Failed to fetch leave history:", data.message);
            }
        } catch (error) {
            console.error("Error fetching leave history:", error);
        }
    };

    const updateLeaveHistoryTables = () => {
        annualLeaveBody.innerHTML = '';
        otherLeaveBody.innerHTML = '';

        leaveHistory.forEach(leave => {
            const row = document.createElement('tr');
            const downloadButton =
                leave.status === 'disetujui'
                    ? `<button class="download-btn" data-id="${leave.id}">Unduh PDF</button>`
                    : `<span class="note-grey-inline">Surat belum tersedia</span>`;

            if (leave.leaveType === 'Cuti Tahunan') {
                row.innerHTML = `
                <td>${leave.submissionDate}</td>
                <td>${leave.startDate}</td>
                <td>${leave.endDate}</td>
                <td>${leave.days} Hari</td>
                <td><span class="status ${leave.status}">${leave.statusText}</span></td>
                <td>${downloadButton}</td>
                `;
                annualLeaveBody.appendChild(row);
            } else {
                row.innerHTML = `
                <td>${leave.leaveType}</td>
                <td>${leave.startDate}</td>
                <td>${leave.endDate}</td>
                <td><span class="status ${leave.status}">${leave.statusText}</span></td>
                <td>${downloadButton}</td>
                `;
                otherLeaveBody.appendChild(row);
            }
        });

        addDownloadButtonListeners();
    };

    const addDownloadButtonListeners = () => {
        document.querySelectorAll('.download-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                const permohonanId = e.target.dataset.id;
                downloadSuratPersetujuan(permohonanId);
            });
        });
    };

    const updateLeaveSummary = () => {
        const leaveType = leaveTypeSelect.value;
        const startDateValue = startDateInput.value;
        const endDateValue = endDateInput.value;

        if (leaveType && startDateValue && endDateValue) {
            const days = calculateDays(startDateValue, endDateValue);
            const formattedStartDate = new Date(startDateValue).toLocaleDateString('id-ID');
            const formattedEndDate = new Date(endDateValue).toLocaleDateString('id-ID');

            let summaryText = `[Mengambil ${leaveType} selama ${days} hari, dari ${formattedStartDate} hingga ${formattedEndDate}.]`;

            if (leaveType === 'Cuti Tahunan') {
                if (days > 3) {
                    summaryText = `<span style="color: red;">Durasi Cuti Tahunan tidak boleh lebih dari 3 hari.</span>`;
                }
            } else if (leaveType === 'Cuti Lahiran') {
                if (days !== 90) {
                    summaryText = `<span style="color: red;">Cuti Lahiran harus 90 hari.</span>`;
                }
            }

            leaveSummaryNote.innerHTML = summaryText;
        } else {
            leaveSummaryNote.textContent = '';
        }
    };

    function formatDate(date) {
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function calculateDays(start, end) {
        if (!start || !end) return 0;
        const startDate = new Date(start);
        const endDate = new Date(end);
        const diffInMilliseconds = endDate.getTime() - startDate.getTime();
        const diffInDays = diffInMilliseconds / (1000 * 60 * 60 * 24);
        return Math.floor(diffInDays) + 1;
    }

    const setDateLimits = () => {
        const leaveType = leaveTypeSelect.value;
        const today = new Date();
        const minStartDate = new Date('2025-01-01');

        if (leaveType === 'Cuti Tahunan') {
            annualLeaveNote.classList.remove('hidden');
        } else {
            annualLeaveNote.classList.add('hidden');
        }
        startDateInput.setAttribute('min', formatDate(minStartDate));

        const startDateValue = startDateInput.value;
        if (!startDateValue) {
            endDateInput.removeAttribute('min');
            endDateInput.removeAttribute('max');
            return;
        }

        const startDate = new Date(startDateValue);
        endDateInput.setAttribute('min', startDateValue);

        if (leaveType === 'Cuti Tahunan') {
            const maxDate = new Date(startDate);
            maxDate.setDate(startDate.getDate() + 2);
            endDateInput.setAttribute('max', formatDate(maxDate));
        } else if (leaveType === 'Cuti Lahiran') {
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 89);
            endDateInput.value = formatDate(endDate);
            endDateInput.setAttribute('min', formatDate(endDate));
            endDateInput.setAttribute('max', formatDate(endDate));
        } else {
            let maxDays;
            switch (leaveType) {
                case 'Cuti Menikah':
                    maxDays = 3;
                    break;
                case 'Cuti Kemalangan':
                case 'Cuti Istri Lahiran':
                    maxDays = 2;
                    break;
                default:
                    endDateInput.removeAttribute('max');
                    return;
            }
            const maxDate = new Date(startDate);
            maxDate.setDate(startDate.getDate() + (maxDays - 1));
            endDateInput.setAttribute('max', formatDate(maxDate));
        }

        if (endDateInput.value && new Date(endDateInput.value) > new Date(endDateInput.getAttribute('max'))) {
            endDateInput.value = '';
        }
        updateLeaveSummary();
    };

    await fetchEmployeeData();
    await fetchLeaveHistory();

    const pageContent = document.querySelector('.page-content');
    if (pageContent) {
        pageContent.style.opacity = '1';
        pageContent.style.transform = 'translateY(0)';
    }

    const cardLogoContainer = document.querySelector('.card-logo-container');
    window.addEventListener('scroll', () => {
        const scrollPosition = window.scrollY;
        const hideThreshold = 200;
        if (scrollPosition > hideThreshold) {
            cardLogoContainer.classList.add('sticky-hide');
        } else {
            cardLogoContainer.classList.remove('sticky-hide');
        }
    });

    leaveTypeSelect.addEventListener('change', () => {
        const selectedLeave = leaveTypeSelect.value;
        if (selectedLeave === 'Cuti Sakit' || selectedLeave === 'Cuti Ibadah') {
            fileUploadGroup.classList.remove('hidden');
        } else {
            fileUploadGroup.classList.add('hidden');
        }
        startDateInput.value = '';
        endDateInput.value = '';
        setDateLimits();
    });

    startDateInput.addEventListener('change', setDateLimits);
    endDateInput.addEventListener('change', updateLeaveSummary);

    fileAttachInput.addEventListener('change', () => {
        if (fileAttachInput.files.length > 0) {
            fileNameDisplay.textContent = fileAttachInput.files[0].name;
        } else {
            fileNameDisplay.textContent = 'Belum ada file dipilih';
        }
    });

    leaveForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (hasPendingApplication) {
            popupModalPending.style.display = 'flex';
            setTimeout(() => { popupModalPending.classList.add('show'); }, 10);
            setTimeout(() => {
                popupModalPending.classList.remove('show');
                setTimeout(() => {
                    popupModalPending.style.display = 'none';
                }, 300);
            }, 3000);
            return;
        }

        const nameFromStorage = sessionStorage.getItem('employeeName');
        const idFromStorage = sessionStorage.getItem('employeeId');
        const unitFromStorage = sessionStorage.getItem('employeeUnit');

        if (!leaveTypeSelect.value || !startDateInput.value || !endDateInput.value || !reasonTextarea.value || !addressTextarea.value || !nameFromStorage || !idFromStorage || !unitFromStorage) {
            alert('Semua bidang harus diisi!');
            return;
        }

        const leaveType = leaveTypeSelect.value;
        const days = calculateDays(startDateInput.value, endDateInput.value);

        if (leaveType === 'Cuti Tahunan') {
            const minDateForAnnualLeave = new Date('2025-01-01');
            if (new Date(startDateInput.value) < minDateForAnnualLeave) {
                alert('Cuti Tahunan hanya bisa diajukan minimal mulai dari Januari 2025.');
                return;
            }
            if (days > 3) {
                alert('Durasi Cuti Tahunan tidak boleh lebih dari 3 hari.');
                return;
            }
            if (annualLeaveQuota - days < 0) {
                alert('Sisa cuti tahunan tidak mencukupi.');
                return;
            }
        } else if (leaveType === 'Cuti Lahiran') {
            if (days !== 90) {
                alert('Cuti Lahiran harus 90 hari.');
                return;
            }
        }
        
        // Mulai loading sebelum API call
        showGlobalLoading(true);

        const formData = {
            jenis_cuti: leaveType,
            tanggal_mulai: startDateInput.value,
            tanggal_selesai: endDateInput.value,
            alasan: reasonTextarea.value,
            alamat: addressTextarea.value,
        };

        try {
            const token = sessionStorage.getItem('api_token');
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
                popupModalSuccess.style.display = 'flex';
                setTimeout(() => { popupModalSuccess.classList.add('show'); }, 10);
                await fetchLeaveHistory();
                await fetchEmployeeData();
                leaveForm.reset();
                setTimeout(() => {
                    popupModalSuccess.classList.remove('show');
                    setTimeout(() => {
                        popupModalSuccess.style.display = 'none';
                    }, 300);
                }, 2500);
            } else {
                alert('Gagal mengajukan permohonan: ' + result.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat mengajukan permohonan.');
            console.error('Error:', error);
        } finally {
            // Sembunyikan loading setelah API call selesai (berhasil atau gagal)
            showGlobalLoading(false);
        }
    });

    // Panggil fungsi ini untuk pertama kali saat halaman dimuat
    setDateLimits();
});