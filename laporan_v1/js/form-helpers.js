/**
 * Form helpers for dynamic required field indication
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add dynamic required field indicators based on selected report types
    const reportTypeCheckboxes = document.querySelectorAll('input[name="reportType[]"]');
    
    reportTypeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateRequiredFieldIndicators);
    });
    
    // Initialize on load
    updateRequiredFieldIndicators();
});

function updateRequiredFieldIndicators() {
    const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
    const isOnlyLaporanKhusus = selectedReports.length === 1 && selectedReports.includes('Laporan Khusus');
    const hasGeneralReports = selectedReports.some(type => type !== 'Laporan Khusus');
    
    // Update Tema field requirement
    const temaField = document.getElementById('inputTema');
    const temaLabel = document.querySelector('label[for="inputTema"]');
    
    if (selectedReports.includes('Laporan Khusus')) {
        temaField.setAttribute('required', 'required');
        if (!temaLabel.querySelector('.text-danger')) {
            temaLabel.insertAdjacentHTML('beforeend', ' <span class="text-danger">*</span>');
        }
    } else {
        temaField.removeAttribute('required');
        const requiredIndicator = temaLabel.querySelector('.text-danger');
        if (requiredIndicator) {
            requiredIndicator.remove();
        }
    }
    
    // Update Patrol Report field requirement
    const patrolReportField = document.getElementById('patrolReport');
    const patrolReportLabel = document.querySelector('label[for="patrolReport"]');
    
    if (hasGeneralReports && !isOnlyLaporanKhusus) {
        patrolReportField.setAttribute('required', 'required');
        if (!patrolReportLabel.querySelector('.text-danger')) {
            patrolReportLabel.insertAdjacentHTML('beforeend', ' <span class="text-danger">*</span>');
        }
    } else {
        patrolReportField.removeAttribute('required');
        const requiredIndicator = patrolReportLabel.querySelector('.text-danger');
        if (requiredIndicator) {
            requiredIndicator.remove();
        }
    }
    
    // Update Patrol Report Khusus field requirement
    const patrolReportKhususField = document.getElementById('patrolReportKhusus');
    const patrolReportKhususLabel = document.querySelector('label[for="patrolReportKhusus"]');
    
    if (selectedReports.includes('Laporan Khusus')) {
        patrolReportKhususField.setAttribute('required', 'required');
        if (!patrolReportKhususLabel.querySelector('.text-danger')) {
            patrolReportKhususLabel.insertAdjacentHTML('beforeend', ' <span class="text-danger">*</span>');
        }
    } else {
        patrolReportKhususField.removeAttribute('required');
        const requiredIndicator = patrolReportKhususLabel.querySelector('.text-danger');
        if (requiredIndicator) {
            requiredIndicator.remove();
        }
    }
    
    // Update step info to show what's required
    updateStepRequirementInfo(selectedReports, isOnlyLaporanKhusus, hasGeneralReports);
}

function updateStepRequirementInfo(selectedReports, isOnlyLaporanKhusus, hasGeneralReports) {
    // Show helpful info in step 3
    let infoDiv = document.getElementById('step3RequirementInfo');
    if (!infoDiv) {
        infoDiv = document.createElement('div');
        infoDiv.id = 'step3RequirementInfo';
        infoDiv.className = 'alert alert-info py-2 px-3 mb-3';
        
        const step3 = document.getElementById('step-3');
        if (step3) {
            step3.insertBefore(infoDiv, step3.querySelector('h5').nextSibling);
        }
    }
    
    let infoContent = '<i class="bi bi-info-circle me-2"></i><strong>Yang perlu diisi:</strong><ul class="mb-0 mt-1 ps-3">';
    
    if (hasGeneralReports && !isOnlyLaporanKhusus) {
        infoContent += '<li>Patrol Report (untuk Laporan KBD, Patroli Landy, Patroli Pagi)</li>';
    }
    
    if (selectedReports.includes('Laporan Khusus')) {
        infoContent += '<li>Patrol Report Khusus</li>';
        infoContent += '<li>Tema Laporan Khusus</li>';
    }
    
    if (selectedReports.includes('Patroli Pagi')) {
        infoContent += '<li>Input Upaya (untuk Patroli Pagi)</li>';
    }
    
    infoContent += '</ul>';
    infoDiv.innerHTML = infoContent;
    
    // Show helpful info in step 4
    let step4InfoDiv = document.getElementById('step4RequirementInfo');
    if (!step4InfoDiv) {
        step4InfoDiv = document.createElement('div');
        step4InfoDiv.id = 'step4RequirementInfo';
        step4InfoDiv.className = 'alert alert-warning py-2 px-3 mb-3';
        
        const step4 = document.getElementById('step-4');
        if (step4) {
            step4.insertBefore(step4InfoDiv, step4.querySelector('h5').nextSibling);
        }
    }
    
    let step4Content = '<i class="bi bi-exclamation-triangle me-2"></i><strong>File yang perlu diupload:</strong><ul class="mb-0 mt-1 ps-3">';
    
    if (hasGeneralReports && !isOnlyLaporanKhusus) {
        step4Content += '<li>Screenshot Patroli (untuk Laporan KBD, Patroli Landy, Patroli Pagi)</li>';
    }
    
    if (selectedReports.includes('Laporan KBD')) {
        step4Content += '<li>File Excel dan Gambar Cipop (untuk Laporan KBD)</li>';
    }
    
    if (selectedReports.includes('Laporan Khusus')) {
        step4Content += '<li>File Excel, Gambar Cipop, dan Screenshot Patroli Khusus (untuk Laporan Khusus)</li>';
    }
    
    if (selectedReports.includes('Patroli Landy')) {
        step4Content += '<li>Screenshot RAS (untuk Patroli Landy)</li>';
    }
    
    if (selectedReports.includes('Patroli Pagi')) {
        step4Content += '<li>Gambar Upaya (untuk Patroli Pagi)</li>';
    }
    
    step4Content += '</ul>';
    step4InfoDiv.innerHTML = step4Content;
}

// Add real-time validation for Tema field
document.addEventListener('DOMContentLoaded', function() {
    const temaField = document.getElementById('inputTema');
    const temaFeedback = document.getElementById('temaValidationFeedback');
    
    if (temaField && temaFeedback) {
        temaField.addEventListener('input', function() {
            const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
            
            if (selectedReports.includes('Laporan Khusus')) {
                if (this.value.trim() === '') {
                    this.classList.add('is-invalid');
                    temaFeedback.textContent = 'Tema laporan khusus harus diisi.';
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    temaFeedback.textContent = '';
                }
            } else {
                this.classList.remove('is-invalid', 'is-valid');
                temaFeedback.textContent = '';
            }
        });
        
        // Also validate when report types change
        document.querySelectorAll('input[name="reportType[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                temaField.dispatchEvent(new Event('input'));
            });
        });
    }
});
