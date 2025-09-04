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
    if (email) document.getElementById('employee-email').textContent = email;

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

    const annualLeaveBody = document.getElementById('annual-leave-body');
    const otherLeaveBody = document.getElementById('other-leave-body');
    const annualLeaveRemaining = document.getElementById('annual-leave-remaining');

    let annualLeaveQuota = 12;
    let leaveHistory = [];
    let hasPendingApplication = false; // Status untuk melacak permohonan yang menunggu

    // Mengambil data sisa cuti dan riwayat dari API
    const fetchEmployeeData = async () => {
        try {
            const token = sessionStorage.getItem('api_token');
            const response = await fetch('/api/karyawan/info', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            if (response.ok) {
                annualLeaveQuota = data.jatah_cuti_tahunan;
                annualLeaveRemaining.textContent = `${annualLeaveQuota} Hari`;
            } else {
                console.error('Failed to fetch employee data:', data.message);
            }
        } catch (error) {
            console.error('Error fetching employee data:', error);
        }
    };

    const fetchLeaveHistory = async () => {
        try {
            const token = sessionStorage.getItem('api_token');
            const response = await fetch('/api/permohonan/history', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            if (response.ok) {
                leaveHistory = data.history.map(item => ({
                    leaveType: item.jenis_cuti,
                    startDate: item.tanggal_mulai,
                    endDate: item.tanggal_selesai,
                    days: item.durasi,
                    status: item.status.toLowerCase(),
                    statusText: item.status,
                    submissionDate: item.created_at.substring(0, 0)
                }));
                updateLeaveHistoryTables();
                // Memeriksa apakah ada permohonan yang menunggu
                hasPendingApplication = leaveHistory.some(leave => leave.status === 'pending');
            } else {
                console.error('Failed to fetch leave history:', data.message);
            }
        } catch (error) {
            console.error('Error fetching leave history:', error);
        }
    };

    const updateLeaveHistoryTables = () => {
        annualLeaveBody.innerHTML = '';
        otherLeaveBody.innerHTML = '';
        let takenAnnualLeave = 0;

        leaveHistory.forEach(leave => {
            const row = document.createElement('tr');
            if (leave.leaveType === 'Cuti Tahunan') {
                takenAnnualLeave += leave.days;
                row.innerHTML = `
                    <td>${leave.submissionDate}</td>
                    <td>${leave.startDate}</td>
                    <td>${leave.endDate}</td>
                    <td>${leave.days} Hari</td>
                    <td><span class="status ${leave.status}">${leave.statusText}</span></td>
                `;
                annualLeaveBody.appendChild(row);
            } else {
                row.innerHTML = `
                    <td>${leave.leaveType}</td>
                    <td>${leave.startDate}</td>
                    <td>${leave.endDate}</td>
                    <td><span class="status ${leave.status}">${leave.statusText}</span></td>
                `;
                otherLeaveBody.appendChild(row);
            }
        });
        const remaining = annualLeaveQuota - takenAnnualLeave;
        annualLeaveRemaining.textContent = `${remaining} Hari`;
    };
    
    await fetchEmployeeData();
    await fetchLeaveHistory();

    // --- Semua logika validasi dan UI dinamis dari file sebelumnya ---
    const pageContent = document.querySelector('.page-content');
    if (pageContent) {
        pageContent.style.opacity = '1';
        pageContent.style.transform = 'translateY(0)';
    }

    const cardLogoContainer = document.querySelector('.card-logo-container');
    window.addEventListener('scroll', () => {
        const scrollPosition = window.scrollY;
        //const hideThreshold = 200; 
        if (scrollPosition > hideThreshold) {
            cardLogoContainer.classList.add('sticky-hide');
        } else {
            cardLogoContainer.classList.remove('sticky-hide');
        }
    });

    leaveTypeSelect.addEventListener('change', () => {
        const selectedLeave = leaveTypeSelect.value;
        if (selectedLeave === 'Sakit' || selectedLeave === 'Ibadah') {
            fileUploadGroup.classList.remove('hidden');
        } else {
            fileUploadGroup.classList.add('hidden');
        }
        startDateInput.value = '';
        endDateInput.value = '';
        setStartDateLimit();
        setEndDateLimit();
    });

    fileAttachInput.addEventListener('change', () => {
        if (fileAttachInput.files.length > 0) {
            fileNameDisplay.textContent = fileAttachInput.files[0].name;
        } else {
            fileNameDisplay.textContent = 'Belum ada file dipilih';
        }
    });

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
        const timeDifference = endDate.getTime() - startDate.getTime();
        const dayDifference = timeDifference / (1000 * 3600 * 24);
        return dayDifference + 1;
    }

    const setStartDateLimit = () => {
        const today = new Date();
        const minStartDate = new Date(today);
        
        if (leaveTypeSelect.value === 'Cuti Tahunan') {
            minStartDate.setDate(today.getDate() + 30);
            annualLeaveNote.classList.remove('hidden');
        } else {
            minStartDate.setDate(today.getDate());
            annualLeaveNote.classList.add('hidden');
        }
        
        startDateInput.setAttribute('min', formatDate(minStartDate));
    };

    setStartDateLimit();

    const setEndDateLimit = () => {
        const leaveType = leaveTypeSelect.value;
        const startDateValue = startDateInput.value;
        
        if (!startDateValue) {
            endDateInput.removeAttribute('min');
            endDateInput.removeAttribute('max');
            endDateInput.value = '';
            return;
        }

        const startDate = new Date(startDateValue);
        endDateInput.setAttribute('min', startDateValue);

        if (leaveType === 'Cuti Lahiran') {
            const endDate = new Date(startDate);
            endDate.setMonth(startDate.getMonth() + 3);
            endDate.setDate(endDate.getDate() - 1);
            
            endDateInput.value = formatDate(endDate);
            endDateInput.setAttribute('min', formatDate(endDate));
            endDateInput.setAttribute('max', formatDate(endDate));
            return;
        }

        let maxDays;
        switch (leaveType) {
            case 'Cuti Tahunan':
            case 'Menikah':
                maxDays = 3;
                break;
            case 'Kemalangan':
            case 'Istri Lahiran':
                maxDays = 2;
                break;
            default:
                endDateInput.removeAttribute('max');
                return;
        }

        const maxDate = new Date(startDate);
        maxDate.setDate(startDate.getDate() + (maxDays - 1));
        endDateInput.setAttribute('max', formatDate(maxDate));

        if (endDateInput.value && new Date(endDateInput.value) > maxDate) {
            endDateInput.value = '';
        }
    };
    
    startDateInput.addEventListener('change', setEndDateLimit);
    leaveTypeSelect.addEventListener('change', setEndDateLimit);

    leaveForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Cek apakah ada permohonan yang menunggu sebelum memproses
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
            if (days > 3) {
                alert('Durasi Cuti Tahunan tidak boleh lebih dari 3 hari.');
                return;
            }
            const today = new Date();
            const minDateForAnnualLeave = new Date(today);
            minDateForAnnualLeave.setDate(today.getDate() + 30);
            if (new Date(startDateInput.value) < minDateForAnnualLeave) {
                alert('Cuti Tahunan hanya bisa diajukan minimal 30 hari dari hari ini.');
                return;
            }
            if (annualLeaveQuota - days < 0) {
                alert('Sisa cuti tahunan tidak mencukupi.');
                return;
            }
        }
        
        const formData = {
            jenis_cuti: leaveType,
            tanggal_mulai: startDateInput.value,
            tanggal_selesai: endDateInput.value,
            alasan: reasonTextarea.value,
            alamat: addressTextarea.value,
            file: fileAttachInput.files[0]
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
                // Tampilkan pop-up "Sukses" setelah permohonan berhasil diajukan
                popupModalSuccess.style.display = 'flex';
                setTimeout(() => { popupModalSuccess.classList.add('show'); }, 10);
                
                setTimeout(async () => {
                    popupModalSuccess.classList.remove('show');
                    await new Promise(resolve => setTimeout(resolve, 300));
                    popupModalSuccess.style.display = 'none';

                    await fetchLeaveHistory();
                    await fetchEmployeeData();
                    leaveForm.reset();
                }, 3000);

            } else {
                alert('Gagal mengajukan permohonan: ' + result.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat mengajukan permohonan.');
            console.error('Error:', error);
        }
    });
});