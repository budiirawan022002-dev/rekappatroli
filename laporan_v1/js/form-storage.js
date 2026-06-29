/**
 * Form Storage Handler
 * Menyimpan dan memulihkan data form dari localStorage
 */

// Save form data to localStorage
function saveFormData() {
    try {
        const formData = {
            // Step 1: Selected report types
            reportTypes: Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
                .map(cb => cb.value),
            
            // Step 2: Date
            tanggal: document.getElementById('tanggal')?.value || '',
            
            // Step 3: Text inputs
            patrolReport: document.getElementById('patrolReport')?.value || '',
            judulLandy: document.getElementById('judulLandy')?.value || '',
            judulLandyCustom: document.getElementById('judulLandyCustom')?.value || '',
            judulBencana: document.getElementById('judulBencana')?.value || '',
            judulBencanaCustom: document.getElementById('judulBencanaCustom')?.value || '',
            temaKhusus: document.getElementById('temaKhusus')?.value || '',
            upayaPatroli: document.getElementById('upayaPatroli')?.value || '',
            
            // Step 3: Profiling data (for Landy)
            profilingNama: document.getElementById('profilingNama')?.value || '',
            profilingJenisKelamin: document.getElementById('profilingJenisKelamin')?.value || '',
            profilingGolonganDarah: document.getElementById('profilingGolonganDarah')?.value || '',
            profilingStatusNikah: document.getElementById('profilingStatusNikah')?.value || '',
            profilingAgama: document.getElementById('profilingAgama')?.value || '',
            profilingLahir: document.getElementById('profilingLahir')?.value || '',
            profilingUmur: document.getElementById('profilingUmur')?.value || '',
            profilingTanggalLahir: document.getElementById('profilingTanggalLahir')?.value || '',
            profilingPekerjaan: document.getElementById('profilingPekerjaan')?.value || '',
            profilingProvinsi: document.getElementById('profilingProvinsi')?.value || '',
            profilingKabupaten: document.getElementById('profilingKabupaten')?.value || '',
            profilingKecamatan: document.getElementById('profilingKecamatan')?.value || '',
            profilingKelurahan: document.getElementById('profilingKelurahan')?.value || '',
            profilingKodePos: document.getElementById('profilingKodePos')?.value || '',
            profilingRtRw: document.getElementById('profilingRtRw')?.value || '',
            profilingAlamat: document.getElementById('profilingAlamat')?.value || '',
            
            // Current step
            currentStep: getCurrentStep(),
            
            // Timestamp
            savedAt: new Date().toISOString()
        };
        
        localStorage.setItem('wizardFormData', JSON.stringify(formData));
        console.log('✅ Form data saved to localStorage');
    } catch (e) {
        console.error('Error saving form data:', e);
    }
}

// Load form data from localStorage
function loadFormData() {
    try {
        const savedData = localStorage.getItem('wizardFormData');
        if (!savedData) return false;
        
        const formData = JSON.parse(savedData);
        
        // Restore Step 1: Report types
        if (formData.reportTypes && formData.reportTypes.length > 0) {
            document.querySelectorAll('input[name="reportType[]"]').forEach(cb => {
                cb.checked = formData.reportTypes.includes(cb.value);
            });
        }
        
        // Restore Step 2: Date
        if (formData.tanggal) {
            const tanggalInput = document.getElementById('tanggal');
            if (tanggalInput) {
                tanggalInput.value = formData.tanggal;
            }
        }
        
        // Restore Step 3: Text inputs
        if (formData.patrolReport) {
            const patrolReportInput = document.getElementById('patrolReport');
            if (patrolReportInput) {
                patrolReportInput.value = formData.patrolReport;
            }
        }
        
        if (formData.judulLandy) {
            const judulLandyInput = document.getElementById('judulLandy');
            if (judulLandyInput) {
                judulLandyInput.value = formData.judulLandy;
                // Trigger change event to show custom input if needed
                if (judulLandyInput.value === 'custom') {
                    const customInput = document.getElementById('judulLandyCustomInput');
                    if (customInput) customInput.classList.remove('d-none');
                }
            }
        }
        
        if (formData.judulLandyCustom) {
            const judulLandyCustomInput = document.getElementById('judulLandyCustom');
            if (judulLandyCustomInput) {
                judulLandyCustomInput.value = formData.judulLandyCustom;
            }
        }
        
        if (formData.judulBencana) {
            const judulBencanaInput = document.getElementById('judulBencana');
            if (judulBencanaInput) {
                judulBencanaInput.value = formData.judulBencana;
                // Trigger change event to show custom input if needed
                if (judulBencanaInput.value === 'custom') {
                    const customInput = document.getElementById('judulBencanaCustomInput');
                    if (customInput) customInput.classList.remove('d-none');
                }
            }
        }
        
        if (formData.judulBencanaCustom) {
            const judulBencanaCustomInput = document.getElementById('judulBencanaCustom');
            if (judulBencanaCustomInput) {
                judulBencanaCustomInput.value = formData.judulBencanaCustom;
            }
        }
        
        if (formData.temaKhusus) {
            const temaKhususInput = document.getElementById('temaKhusus');
            if (temaKhususInput) {
                temaKhususInput.value = formData.temaKhusus;
            }
        }
        
        if (formData.upayaPatroli) {
            const upayaPatroliInput = document.getElementById('upayaPatroli');
            if (upayaPatroliInput) {
                upayaPatroliInput.value = formData.upayaPatroli;
            }
        }
        
        // Restore profiling data
        const profilingFields = [
            'profilingNama', 'profilingJenisKelamin', 'profilingGolonganDarah',
            'profilingStatusNikah', 'profilingAgama', 'profilingLahir', 'profilingUmur',
            'profilingTanggalLahir', 'profilingPekerjaan', 'profilingProvinsi',
            'profilingKabupaten', 'profilingKecamatan', 'profilingKelurahan',
            'profilingKodePos', 'profilingRtRw', 'profilingAlamat'
        ];
        
        profilingFields.forEach(field => {
            if (formData[field]) {
                const input = document.getElementById(field);
                if (input) {
                    input.value = formData[field];
                }
            }
        });
        
        // Restore current step if needed
        if (formData.currentStep && formData.currentStep > 1) {
            // Don't auto-navigate, let user continue from where they left
        }
        
        console.log('✅ Form data loaded from localStorage');
        return true;
    } catch (e) {
        console.error('Error loading form data:', e);
        return false;
    }
}

// Clear form data from localStorage
function clearFormData() {
    try {
        localStorage.removeItem('wizardFormData');
        console.log('✅ Form data cleared from localStorage');
    } catch (e) {
        console.error('Error clearing form data:', e);
    }
}

// Get current step number
function getCurrentStep() {
    const steps = document.querySelectorAll('.wizard-step');
    for (let i = 0; i < steps.length; i++) {
        if (!steps[i].classList.contains('d-none')) {
            return i + 1;
        }
    }
    return 1;
}

// Initialize form storage on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load saved data
    const dataLoaded = loadFormData();
    
    if (dataLoaded) {
        // Trigger change events to update UI visibility
        document.querySelectorAll('input[name="reportType[]"]').forEach(cb => {
            if (cb.checked) {
                cb.dispatchEvent(new Event('change'));
            }
        });
        
        // Trigger change for judul dropdowns
        const judulLandy = document.getElementById('judulLandy');
        if (judulLandy && judulLandy.value) {
            judulLandy.dispatchEvent(new Event('change'));
        }
        
        const judulBencana = document.getElementById('judulBencana');
        if (judulBencana && judulBencana.value) {
            judulBencana.dispatchEvent(new Event('change'));
        }
    }
    
    // Auto-save on input changes
    const form = document.getElementById('wizardForm');
    if (form) {
        // Save on any input change
        form.addEventListener('input', function() {
            saveFormData();
        });
        
        // Save on checkbox change
        form.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox' || e.target.type === 'select-one') {
                saveFormData();
            }
        });
        
        // Don't clear on submit - only clear after successful response
        // Clearing will be handled in ajax-handler.js after successful submission
    }
});

// Export functions for use in other scripts
if (typeof window !== 'undefined') {
    window.saveFormData = saveFormData;
    window.loadFormData = loadFormData;
    window.clearFormData = clearFormData;
}

