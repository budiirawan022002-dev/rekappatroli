// Custom JavaScript functions for Rekap Hastag Application

/**
 * Show specific wizard step
 * @param {number} stepNumber 
 */
function showStep(stepNumber) {
    const allSteps = document.querySelectorAll('.wizard-step');
    allSteps.forEach((step, index) => {
        step.classList.toggle('d-none', index !== stepNumber - 1);
    });
}

/**
 * Navigate to next step with validation
 * @param {number} step - Current step number
 */
function nextStep(step) {
    console.log('nextStep called with step:', step);
    
    // Get selected report types
    const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
        .map(cb => cb.value);
    
    const reportsNeedPatrolReport = ['Laporan KBD', 'Patroli Landy', 'Patroli Pagi', 'Patroli Bencana', 'Laporan MBG Lengkap'];
    const hasGeneralReports = selectedReports.some(type => reportsNeedPatrolReport.includes(type));
    const isOnlyLaporanKhusus = selectedReports.length === 1 && selectedReports.includes('Laporan Khusus');
    const isOnlyLaporanPpt = selectedReports.length === 1 && selectedReports.includes('Laporan PPT');

    // Step 1 validation: At least one report type must be selected
    if (step === 1) {
        if (selectedReports.length === 0) {
            alert('Pilih setidaknya satu jenis laporan.');
            return;
        }
        
        // Check if Laporan ENGAGEMENT is selected - redirect immediately
        if (selectedReports.includes('Laporan ENGAGEMENT')) {
            // If only ENGAGEMENT is selected, redirect directly
            if (selectedReports.length === 1) {
                window.location.href = 'laporan_engagement.php';
                return;
            } else {
                // If selected with other types, show alert and redirect
                if (confirm('Laporan ENGAGEMENT harus diproses secara terpisah. Anda akan diarahkan ke halaman Laporan ENGAGEMENT. Lanjutkan?')) {
                    window.location.href = 'laporan_engagement.php';
                }
                return;
            }
        }
    }

    // Step 2 validation: Date must be filled
    if (step === 2) {
        const tanggal = document.getElementById('tanggal').value;
        if (!tanggal) {
            alert('Tanggal laporan harus diisi.');
            return;
        }
        
        // Configure Step 3 forms based on selected report types
        console.log('Configuring Step 3 for report types:', selectedReports);
        
        // Show/hide patrol report umum (for all except Laporan Khusus only)
        const step3PatrolReportUmum = document.getElementById('step3-patrolReportUmum');
        if (step3PatrolReportUmum) {
            const shouldHidePatrolUmum = isOnlyLaporanKhusus || !hasGeneralReports;
            step3PatrolReportUmum.classList.toggle('d-none', shouldHidePatrolUmum);
            
            const patrolReportElement = document.getElementById('patrolReport');
            if (patrolReportElement) {
                if (shouldHidePatrolUmum) {
                    patrolReportElement.value = '';
                    patrolReportElement.removeAttribute('required');
                } else {
                    patrolReportElement.setAttribute('required', 'required');
                }
            }
        }
        
        // Show/hide input upaya (Patroli Pagi)
        const step3InputUpaya = document.getElementById('step3-inputUpaya');
        if (step3InputUpaya) {
            step3InputUpaya.classList.toggle('d-none', !selectedReports.includes('Patroli Pagi'));
        }
        
        // Show/hide judul Landy
        const step3JudulLandy = document.getElementById('step3-judulLandy');
        if (step3JudulLandy) {
            const isLandySelected = selectedReports.includes('Patroli Landy');
            step3JudulLandy.classList.toggle('d-none', !isLandySelected);
            
            console.log('Patroli Landy selected:', isLandySelected);
            
            // Reset dropdown dan custom input jika tidak dipilih
            if (!isLandySelected) {
                const judulLandySelect = document.getElementById('judulLandy');
                const judulLandyCustom = document.getElementById('judulLandyCustom');
                const judulLandyCustomInput = document.getElementById('judulLandyCustomInput');
                
                if (judulLandySelect) {
                    judulLandySelect.value = '';
                }
                if (judulLandyCustom) {
                    judulLandyCustom.value = '';
                }
                if (judulLandyCustomInput) {
                    judulLandyCustomInput.classList.add('d-none');
                }
            }
        }
        
        // Show/hide judul MBG Lengkap
        const step3JudulMbgLengkap = document.getElementById('step3-judulMbgLengkap');
        if (step3JudulMbgLengkap) {
            const isMbgLengkapSelected = selectedReports.includes('Laporan MBG Lengkap');
            step3JudulMbgLengkap.classList.toggle('d-none', !isMbgLengkapSelected);

            if (!isMbgLengkapSelected) {
                const judulMbgSelect = document.getElementById('judulMbgLengkap');
                const judulMbgCustom = document.getElementById('judulMbgLengkapCustom');
                const judulMbgCustomInput = document.getElementById('judulMbgLengkapCustomInput');
                if (judulMbgSelect) judulMbgSelect.value = '';
                if (judulMbgCustom) judulMbgCustom.value = '';
                if (judulMbgCustomInput) judulMbgCustomInput.classList.add('d-none');
            }
        }

        // Show/hide judul Bencana
        const step3JudulBencana = document.getElementById('step3-judulBencana');
        if (step3JudulBencana) {
            const isBencanaSelected = selectedReports.includes('Patroli Bencana');
            step3JudulBencana.classList.toggle('d-none', !isBencanaSelected);
            
            console.log('Patroli Bencana selected:', isBencanaSelected);
            
            // Reset dropdown dan custom input jika tidak dipilih
            if (!isBencanaSelected) {
                const judulBencanaSelect = document.getElementById('judulBencana');
                const judulBencanaCustom = document.getElementById('judulBencanaCustom');
                const judulBencanaCustomInput = document.getElementById('judulBencanaCustomInput');
                
                if (judulBencanaSelect) {
                    judulBencanaSelect.value = '';
                }
                if (judulBencanaCustom) {
                    judulBencanaCustom.value = '';
                }
                if (judulBencanaCustomInput) {
                    judulBencanaCustomInput.classList.add('d-none');
                }
            }
        }
        
        // Show/hide laporan khusus
        const step3LaporanKhusus = document.getElementById('step3-laporanKhusus');
        if (step3LaporanKhusus) {
            step3LaporanKhusus.classList.toggle('d-none', !selectedReports.includes('Laporan Khusus'));
        }
        
        // Configure Step 4 upload fields
        const step4LaporanKbd = document.getElementById('step4-laporanKbd');
        if (step4LaporanKbd) {
            const showKbd = selectedReports.includes('Laporan KBD') || selectedReports.includes('Laporan MBG Lengkap');
            step4LaporanKbd.classList.toggle('d-none', !showKbd);
        }
        
        const step4LaporanKhusus = document.getElementById('step4-laporanKhusus');
        if (step4LaporanKhusus) {
            step4LaporanKhusus.classList.toggle('d-none', !selectedReports.includes('Laporan Khusus'));
        }

        const step4LaporanPpt = document.getElementById('step4-laporanPpt');
        if (step4LaporanPpt) {
            step4LaporanPpt.classList.toggle('d-none', !selectedReports.includes('Laporan PPT'));
        }
        
        const shouldHideScreenshotPatrolUmum = isOnlyLaporanKhusus || !hasGeneralReports;
        const step4ScreenshotPatrolUmum = document.getElementById('step4-screenshotPatrolUmum');
        if (step4ScreenshotPatrolUmum) {
            step4ScreenshotPatrolUmum.classList.toggle('d-none', shouldHideScreenshotPatrolUmum);
        }
        
        const step4PatroliLandyPagi = document.getElementById('step4-patroliLandyPagi');
        if (step4PatroliLandyPagi) {
            const showLandyPagi = selectedReports.includes('Patroli Landy') || selectedReports.includes('Patroli Pagi') || selectedReports.includes('Patroli Bencana') || selectedReports.includes('Laporan MBG Lengkap');
            step4PatroliLandyPagi.classList.toggle('d-none', !showLandyPagi);
        }
        
        // Show/hide upaya section (only for Patroli Pagi)
        const upayaPatroliSection = document.getElementById('upayaPatroliSection');
        if (upayaPatroliSection) {
            // Upaya hanya untuk Pagi, sembunyikan jika hanya Landy atau hanya Bencana
            const isOnlyLandy = selectedReports.length === 1 && selectedReports.includes('Patroli Landy');
            const isOnlyBencana = selectedReports.length === 1 && selectedReports.includes('Patroli Bencana');
            const isOnlyMbgLengkap = selectedReports.length === 1 && selectedReports.includes('Laporan MBG Lengkap');
            const isLandyAndBencana = selectedReports.includes('Patroli Landy') && selectedReports.includes('Patroli Bencana') && selectedReports.length === 2;
            
            if (isOnlyLandy || isOnlyBencana || isLandyAndBencana || isOnlyMbgLengkap) {
                upayaPatroliSection.classList.add('d-none');
            } else {
                upayaPatroliSection.classList.toggle('d-none', !selectedReports.includes('Patroli Pagi'));
            }
        }
        
        // Show/hide RAS section for Landy and Bencana
        const landyRasScreenshotSection = document.getElementById('landyRasScreenshotSection');
        if (landyRasScreenshotSection) {
            const isLandyOrBencanaOrMbg = selectedReports.includes('Patroli Landy') || selectedReports.includes('Patroli Bencana') || selectedReports.includes('Laporan MBG Lengkap');
            landyRasScreenshotSection.classList.toggle('d-none', !isLandyOrBencanaOrMbg);
        }
        
        // Show/hide Profiling Screenshot section for Landy and Bencana
        const landyProfilingScreenshotSection = document.getElementById('landyProfilingScreenshotSection');
        if (landyProfilingScreenshotSection) {
            const isLandyOrBencanaOrMbg = selectedReports.includes('Patroli Landy') || selectedReports.includes('Patroli Bencana') || selectedReports.includes('Laporan MBG Lengkap');
            landyProfilingScreenshotSection.classList.toggle('d-none', !isLandyOrBencanaOrMbg);
        }
        
        // Hide Landy profiling data form completely (not needed - using multi-line profiling in textarea)
        const landyProfilingDataSection = document.getElementById('landyProfilingDataSection');
        if (landyProfilingDataSection) {
            // Always hide the form - not needed anymore
            landyProfilingDataSection.classList.add('d-none');
            landyProfilingDataSection.style.display = 'none';
            
            // Get Landy and Bencana selection status
            const isLandySelected = selectedReports.includes('Patroli Landy');
            const isBencanaSelected = selectedReports.includes('Patroli Bencana');
            const isLandyOrBencana = isLandySelected || isBencanaSelected;
            
            // ALWAYS remove ALL required attributes untuk profiling fields
            // Akan di-restore nanti jika Landy dipilih
            const profilingInputs = landyProfilingDataSection.querySelectorAll('input, textarea, select');
            console.log(`🔧 Removing required from ${profilingInputs.length} profiling inputs. Landy selected: ${isLandySelected}, Bencana selected: ${isBencanaSelected}`);
            
            profilingInputs.forEach(input => {
                // HAPUS required dulu
                input.removeAttribute('required');
                
                if (isLandyOrBencana) {
                    // HANYA restore required JIKA Landy atau Bencana dipilih
                    const fieldName = input.name || input.id;
                    const requiredFields = ['profilingNama', 'profilingJenisKelamin', 'profilingUmur', 
                                          'profilingPekerjaan', 'profilingProvinsi', 'profilingKabupaten', 'profilingAlamat'];
                    if (requiredFields.some(f => fieldName && fieldName.includes(f))) {
                        input.setAttribute('required', 'required');
                        console.log(`  ✅ Set required for: ${fieldName}`);
                    }
                } else {
                    console.log(`  ❌ Removed required from: ${input.name || input.id}`);
                }
            });
        }
        
        // Show/hide Landy/Bencana format help in Step 3
        const landyFormatHelp = document.getElementById('landyFormatHelp');
        if (landyFormatHelp) {
            const isLandyOrBencanaOrMbg = selectedReports.includes('Patroli Landy') || selectedReports.includes('Patroli Bencana') || selectedReports.includes('Laporan MBG Lengkap');
            landyFormatHelp.classList.toggle('d-none', !isLandyOrBencanaOrMbg);
        }

        // For PPT-only flow: skip step 3 and go directly to upload section
        if (isOnlyLaporanPpt) {
            console.log('PPT-only selected, skipping step 3 and moving to step 4');
            showStep(4);
            return;
        }
    }

    // Move to next step
    console.log('Moving to step:', step + 1);
    showStep(step + 1);
}

/**
 * Navigate to previous step
 * @param {number} step - Current step number
 */
function prevStep(step) {
    showStep(step - 1);
}

/**
 * Validate file count
 * @param {HTMLInputElement} input 
 * @param {number} maxFiles 
 */
function validateFileCount(input, maxFiles) {
    if (input.files.length > maxFiles) {
        alert(`Maksimal ${maxFiles} file yang dapat diupload.`);
        input.value = '';
        return false;
    }
    return true;
}

/**
 * Preview uploaded files
 * @param {HTMLInputElement} input 
 * @param {string} previewContainerId 
 */
function previewFiles(input, previewContainerId) {
    const previewContainer = document.getElementById(previewContainerId);
    if (!previewContainer) return;
    
    previewContainer.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-preview-item';
            fileItem.innerHTML = `
                <small class="text-muted">
                    <i class="bi bi-file-earmark"></i> ${file.name} (${(file.size / 1024).toFixed(2)} KB)
                </small>
            `;
            previewContainer.appendChild(fileItem);
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Custom.js loaded and initialized');
    
    // Show first step by default
    showStep(1);
    
    // Initialize profiling form required attributes based on current selection
    const initializeProfilingForm = () => {
        const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
            .map(c => c.value);
        const landyProfilingDataSection = document.getElementById('landyProfilingDataSection');
        
        if (landyProfilingDataSection) {
            // Always hide the form - not needed anymore
            landyProfilingDataSection.classList.add('d-none');
            landyProfilingDataSection.style.display = 'none';
            const profilingInputs = landyProfilingDataSection.querySelectorAll('input, textarea, select');
            
            // Get Landy and Bencana selection status
            const isLandySelected = selectedReports.includes('Patroli Landy');
            const isBencanaSelected = selectedReports.includes('Patroli Bencana');
            
            console.log(`🔧 [PageLoad] Removing ALL required from ${profilingInputs.length} inputs. Landy: ${isLandySelected}, Bencana: ${isBencanaSelected}`);
            
            // HAPUS SEMUA required attributes dulu
            profilingInputs.forEach(input => {
                input.removeAttribute('required');
            });
            
            console.log('✅ Initial profiling form state set. ALL required removed. Landy selected:', isLandySelected);
        }
    };
    
    // Call on load
    initializeProfilingForm();
    
    // Initialize report type change handlers
    const reportTypeCheckboxes = document.querySelectorAll('input[name="reportType[]"]');
    reportTypeCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
                .map(c => c.value);
            
            console.log('Report type changed:', selectedReports);
            
            // Handle Landy/Bencana profiling form required attributes
            const landyProfilingDataSection = document.getElementById('landyProfilingDataSection');
            if (landyProfilingDataSection) {
                // Always hide the form - not needed anymore
                landyProfilingDataSection.classList.add('d-none');
                landyProfilingDataSection.style.display = 'none';
                
                // Get Landy and Bencana selection status
                const isLandySelected = selectedReports.includes('Patroli Landy');
                const isBencanaSelected = selectedReports.includes('Patroli Bencana');
                
                // ALWAYS remove ALL required attributes first
                const profilingInputs = landyProfilingDataSection.querySelectorAll('input, textarea, select');
                console.log(`🔧 [CheckboxChange] Removing required from ${profilingInputs.length} inputs. Landy: ${isLandySelected}, Bencana: ${isBencanaSelected}`);
                
                profilingInputs.forEach(input => {
                    // HAPUS required dulu
                    input.removeAttribute('required');
                    
                    if (isLandySelected) {
                        // HANYA restore required JIKA Landy dipilih (Bencana menggunakan multi-line profiling di textarea)
                        const fieldName = input.name || input.id;
                        const requiredFields = ['profilingNama', 'profilingJenisKelamin', 'profilingUmur', 
                                              'profilingPekerjaan', 'profilingProvinsi', 'profilingKabupaten', 'profilingAlamat'];
                        if (requiredFields.some(f => fieldName && fieldName.includes(f))) {
                            input.setAttribute('required', 'required');
                        }
                    }
                });
                
                console.log('✅ Profiling form required attributes updated. Landy selected:', isLandySelected, 'Bencana selected:', isBencanaSelected);
            }
        });
    });
    
    console.log('nextStep function available:', typeof nextStep);
});

// Expose functions globally
window.nextStep = nextStep;
window.prevStep = prevStep;
window.showStep = showStep;
window.validateFileCount = validateFileCount;
window.previewFiles = previewFiles;

console.log('Custom.js file loaded - functions defined');




