/**
 * form-validation.js
 * Berisi fungsi-fungsi untuk validasi form dan interaksi UI
 */

document.addEventListener('DOMContentLoaded', function() {
    // Patrol Report format validation on blur
    const patrolReport = document.getElementById('patrolReport');
    if (patrolReport) {
        patrolReport.addEventListener('blur', function() {
            // Check if Patroli Landy or Patroli Bencana is selected to determine expected field count
            const isPatroliLandy = document.querySelector('input[name="reportType[]"][value="Patroli Landy"]')?.checked;
            const isPatroliBencana = document.querySelector('input[name="reportType[]"][value="Patroli Bencana"]')?.checked;
            const isMbgLengkap = document.querySelector('input[name="reportType[]"][value="Laporan MBG Lengkap"]')?.checked;
            const isLandyOrBencana = isPatroliLandy || isPatroliBencana || isMbgLengkap;
            const linesPerBlock = isLandyOrBencana ? 9 : 4;
            
            console.log('PatrolReport blur validation - isPatroliLandy:', isPatroliLandy, 'isPatroliBencana:', isPatroliBencana, 'linesPerBlock:', linesPerBlock);
            
            // DETECT MULTI-LINE PROFILING FORMAT - Skip validation if detected
            // Support both old format (profiling:\nNama:) and new format (profiling:\nNik: or profiling:\nKK:)
            // Simple pattern: profiling: followed by newline and any field:value pattern (more flexible)
            const hasMultiLineProfiling = /profiling\s*:\s*\n\s*[A-Za-z\s\/\-]+\s*:/i.test(patrolReport.value);
            if (hasMultiLineProfiling && isLandyOrBencana) {
                console.log('✅ Multi-line profiling format detected - Skip validation');
                let preview = document.getElementById('patrolReportPreview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'patrolReportPreview';
                    patrolReport.parentNode.appendChild(preview);
                }
                preview.innerHTML = `
<div class="alert alert-info mt-2" role="alert">
  <i class="bi bi-info-circle-fill me-2"></i>
  <strong>Format Multi-Line Profiling Terdeteksi!</strong> Sistem akan memproses format profiling terstruktur.
</div>`;
                return; // Skip standard validation
            }
            
            const lines = patrolReport.value.split('\n');
            let html = '';
            let hasError = false;
            let filtered = lines.map(l => l.trim()).filter(l => l !== '');
            for (let i = 0, j = 0; i < lines.length; i++) {
                const val = lines[i].trim();
                if (val === '') {
                    html += `<div style="height:1.5em"></div>`;
                    continue;
                }
                let blockIdx = Math.floor(j / linesPerBlock);
                let inBlockIdx = j % linesPerBlock;
                let isError = false;
                if (filtered.length % linesPerBlock !== 0 && blockIdx === Math.floor(filtered.length / linesPerBlock)) {
                    isError = true;
                    hasError = true;
                } else if (filtered.length >= linesPerBlock && blockIdx < Math.floor(filtered.length / linesPerBlock)) {
                    isError = false;
                } else if (filtered.length >= linesPerBlock && blockIdx === Math.floor(filtered.length / linesPerBlock) && inBlockIdx < filtered.length % linesPerBlock) {
                    isError = true;
                    hasError = true;
                }
                if (isError) {
                    html += `<div style="color:#fff;background:#b30000;padding:2px 6px;border-radius:3px;margin-bottom:2px;">${lines[i].replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`;
                } else {
                    html += `<div style="color:#fff;background:#222;padding:2px 6px;border-radius:3px;margin-bottom:2px;">${lines[i].replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`;
                }
                j++;
            }
            if (filtered.length % linesPerBlock !== 0) hasError = true;
            let preview = document.getElementById('patrolReportPreview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'patrolReportPreview';
                patrolReport.parentNode.appendChild(preview);
            }
            if (hasError && filtered.length > 0) {
                const formatMessage = isLandyOrBencana 
                    ? '<b>Setiap laporan harus terdiri dari 9 baris:</b><br>nama akun<br>link<br>kategori<br>narasi<br>profiling<br>tanggal_postingan<br>wilayah<br>korelasi<br>afiliasi<br><br>'
                    : '<b>Setiap laporan harus terdiri dari 4 baris:</b><br>nama akun<br>link<br>kategori<br>narasi<br><br>';
                preview.innerHTML = `
<div class="accordion mt-2" id="accordionPatrolReportError">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingPatrolReportError">
      <button class="accordion-button collapsed bg-danger text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePatrolReportError" aria-expanded="false" aria-controls="collapsePatrolReportError">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Format Patrol Report tidak sesuai! Klik untuk detail.
      </button>
    </h2>
    <div id="collapsePatrolReportError" class="accordion-collapse collapse" aria-labelledby="headingPatrolReportError" data-bs-parent="#accordionPatrolReportError">
      <div class="accordion-body">
        ${formatMessage}
        Periksa baris yang berwarna merah.<hr>${html}
      </div>
    </div>
  </div>
</div>`;
            } else if (filtered.length > 0) {
                // Show success message when format is correct
                const accountCount = filtered.length / linesPerBlock;
                const formatType = isLandyOrBencana ? '9 baris (Patroli Landy/Bencana)' : '4 baris';
                preview.innerHTML = `
<div class="alert alert-success mt-2" role="alert">
  <i class="bi bi-check-circle-fill me-2"></i>
  <strong>Format Sudah Benar!</strong> Terdeteksi ${accountCount} akun dengan format ${formatType} per akun.
</div>`;
            } else {
                preview.innerHTML = '';
            }
        });
        
        // Re-validate when report type changes
        const reportTypeCheckboxes = document.querySelectorAll('input[name="reportType[]"]');
        reportTypeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (patrolReport.value.trim()) {
                    patrolReport.dispatchEvent(new Event('blur'));
                }
            });
        });
    }

    // Upaya format validation on blur
    const inputUpaya = document.getElementById('inputUpaya');
    if (inputUpaya) {
        inputUpaya.addEventListener('blur', function() {
            const lines = inputUpaya.value.split('\n');
            let html = '';
            let hasError = false;
            let filtered = lines.map(l => l.trim()).filter(l => l !== '');
            for (let i = 0, j = 0; i < lines.length; i++) {
                const val = lines[i].trim();
                if (val === '') {
                    html += `<div style="height:1.5em"></div>`;
                    continue;
                }
                let blockIdx = Math.floor(j / 3);
                let inBlockIdx = j % 3;
                let isError = false;
                if (filtered.length % 3 !== 0 && blockIdx === Math.floor(filtered.length / 3)) {
                    isError = true;
                    hasError = true;
                } else if (filtered.length >= 3 && blockIdx < Math.floor(filtered.length / 3)) {
                    isError = false;
                } else if (filtered.length >= 3 && blockIdx === Math.floor(filtered.length / 3) && inBlockIdx < filtered.length % 3) {
                    isError = true;
                    hasError = true;
                }
                if (isError) {
                    html += `<div style="color:#fff;background:#b30000;padding:2px 6px;border-radius:3px;margin-bottom:2px;">${lines[i].replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`;
                } else {
                    html += `<div style="color:#fff;background:#222;padding:2px 6px;border-radius:3px;margin-bottom:2px;">${lines[i].replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>`;
                }
                j++;
            }
            if (filtered.length % 3 !== 0) hasError = true;
            let preview = document.getElementById('inputUpayaPreview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'inputUpayaPreview';
                inputUpaya.parentNode.appendChild(preview);
            }
            if (hasError && filtered.length > 0) {
                preview.innerHTML = `
<div class="accordion mt-2" id="accordionUpayaError">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingUpayaError">
      <button class="accordion-button collapsed bg-danger text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUpayaError" aria-expanded="false" aria-controls="collapseUpayaError">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Format Input Upaya tidak sesuai! Klik untuk detail.
      </button>
    </h2>
    <div id="collapseUpayaError" class="accordion-collapse collapse" aria-labelledby="headingUpayaError" data-bs-parent="#accordionUpayaError">
      <div class="accordion-body">
        <b>Setiap upaya harus terdiri dari 3 baris:</b><br>nama akun<br>link<br>narasi<br><br>
        Periksa baris yang berwarna merah.<hr>${html}
      </div>
    </div>
  </div>
</div>`;
            } else if (filtered.length > 0) {
                // Show success message when format is correct
                const upayaCount = filtered.length / 3;
                preview.innerHTML = `
<div class="alert alert-success mt-2" role="alert">
  <i class="bi bi-check-circle-fill me-2"></i>
  <strong>Format Sudah Benar!</strong> Terdeteksi ${upayaCount} upaya dengan format 3 baris per upaya.
</div>`;
            } else {
                preview.innerHTML = '';
            }
        });
    }

    // NOTE: Form submission is handled by ajax-handler.js
    // This file only handles real-time validation (blur events)
    
    // Custom JS for wizard form and validation - Moved from custom.js
    function initReportTypeSelection() {
        document.querySelectorAll('input[name="reportType[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selected = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
                
                // Show/hide Patroli Pagi upaya input
                const upayaInput = document.getElementById('step3-inputUpaya');
                if (upayaInput) {
                    upayaInput.classList.toggle('d-none', !selected.includes('Patroli Pagi'));
                }
                
                // Show/hide KBD section
                const kbdSection = document.getElementById('step4-laporanKbd');
                if (kbdSection) {
                    kbdSection.classList.toggle('d-none', !(selected.includes('Laporan KBD') || selected.includes('Laporan MBG Lengkap')));
                }
                
                // Show/hide Landy/Pagi/Bencana section
                const landyPagiSection = document.getElementById('step4-patroliLandyPagi');
                if (landyPagiSection) {
                    const showSection = selected.includes('Patroli Landy') || selected.includes('Patroli Pagi') || selected.includes('Patroli Bencana') || selected.includes('Laporan MBG Lengkap');
                    landyPagiSection.classList.toggle('d-none', !showSection);
                    console.log('step4-patroliLandyPagi visibility - Landy:', selected.includes('Patroli Landy'), 'Pagi:', selected.includes('Patroli Pagi'), 'Bencana:', selected.includes('Patroli Bencana'), 'Show:', showSection);
                }
                  // Specific handling for upaya section (only for Patroli Pagi)
                const upayaSection = document.getElementById('upayaPatroliSection');
                if (upayaSection) {
                    // Hide upaya section if only Landy or only Bencana is selected (upaya hanya untuk Pagi)
                    const isOnlyLandy = selected.length === 1 && selected.includes('Patroli Landy');
                    const isOnlyBencana = selected.length === 1 && selected.includes('Patroli Bencana');
                    const isLandyAndBencana = selected.includes('Patroli Landy') && selected.includes('Patroli Bencana') && selected.length === 2;
                    
                    if (isOnlyLandy || isOnlyBencana || isLandyAndBencana) {
                        upayaSection.classList.add('d-none');
                        if (typeof debugLog !== 'undefined') {
                            debugLog('initReportTypeSelection: Hiding upaya section (only for Pagi)');
                        }
                    } else {
                        upayaSection.classList.toggle('d-none', !selected.includes('Patroli Pagi'));
                    }
                }
                
                // RAS section for Landy and Bencana
                const rasSection = document.getElementById('landyRasScreenshotSection');
                if (rasSection) {
                    const showRas = selected.includes('Patroli Landy') || selected.includes('Patroli Bencana') || selected.includes('Laporan MBG Lengkap');
                    rasSection.classList.toggle('d-none', !showRas);
                    if (typeof debugLog !== 'undefined') {
                        debugLog('RAS section visibility - Landy:', selected.includes('Patroli Landy'), 'Bencana:', selected.includes('Patroli Bencana'), 'Show:', showRas);
                    }
                }
                
                // Profiling Screenshot section for Landy and Bencana
                const profilingSection = document.getElementById('landyProfilingScreenshotSection');
                if (profilingSection) {
                    const showProfiling = selected.includes('Patroli Landy') || selected.includes('Patroli Bencana') || selected.includes('Laporan MBG Lengkap');
                    profilingSection.classList.toggle('d-none', !showProfiling);
                    if (typeof debugLog !== 'undefined') {
                        debugLog('Profiling section visibility - Landy:', selected.includes('Patroli Landy'), 'Bencana:', selected.includes('Patroli Bencana'), 'Show:', showProfiling);
                    }
                }
            });
        });
          // Trigger change event to initialize state
        const checkboxes = document.querySelectorAll('input[name="reportType[]"]');
        if (checkboxes.length > 0) {
            if (typeof debugLog !== 'undefined') {
                debugLog('Initializing report type selection');
            }
            
            // Force a change event on all checkboxes to initialize UI properly
            checkboxes.forEach(checkbox => {
                const event = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(event);
            });
        }
    }
    
    // Call initialization function
    initReportTypeSelection();
    function toggleCipopImageInput() {
        const type = document.querySelector('input[name="cipopImageType"]:checked').value;
        document.getElementById('cipopUploadFileGroup').classList.toggle('d-none', type !== 'upload');
        document.getElementById('cipopScreenshotLinkGroup').classList.toggle('d-none', type !== 'screenshot');
    }
    const radios = document.querySelectorAll('input[name="cipopImageType"]');
    radios.forEach(r => r.addEventListener('change', toggleCipopImageInput));
    toggleCipopImageInput();

    function togglePatroliScreenshotInput() {
        const type = document.querySelector('input[name="patroliScreenshotType"]:checked').value;
        document.getElementById('patroliScreenshotUploadFileGroup').classList.toggle('d-none', type !== 'upload');
        document.getElementById('patroliScreenshotLinkWarning').classList.toggle('d-none', type !== 'screenshot');
    }    const radiosPatroli = document.querySelectorAll('input[name="patroliScreenshotType"]');
    radiosPatroli.forEach(r => r.addEventListener('change', togglePatroliScreenshotInput));
    togglePatroliScreenshotInput();
    
    function toggleUpayaScreenshotInput() {
        const selected = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
        const upayaScreenshotTypeGroup = document.getElementById('upayaScreenshotTypeGroup');
        const upayaScreenshotLinkRadio = document.getElementById('upayaScreenshotLinkRadio');
        const upayaSection = document.getElementById('upayaPatroliSection');
        
        // Debug log for toggling upaya inputs
        if (typeof debugLog !== 'undefined') {
            debugLog('Toggle upaya inputs - Selected report types:', selected);
            debugLog('Toggle upaya inputs - Upaya section exists:', Boolean(upayaSection));
        }
        
        // Only show upaya options when Patroli Pagi is selected
        if (selected.includes('Patroli Pagi')) {
            if (upayaScreenshotTypeGroup) upayaScreenshotTypeGroup.classList.remove('d-none');
            if (upayaScreenshotLinkRadio) upayaScreenshotLinkRadio.classList.remove('d-none');
            if (upayaSection) upayaSection.classList.remove('d-none');
        } else {
            if (upayaScreenshotTypeGroup) upayaScreenshotTypeGroup.classList.add('d-none');
            if (upayaScreenshotLinkRadio) upayaScreenshotLinkRadio.classList.add('d-none');
            
            // For Landy only, hide the entire upaya section
            if (selected.length === 1 && selected.includes('Patroli Landy') && upayaSection) {
                upayaSection.classList.add('d-none');
                if (typeof debugLog !== 'undefined') {
                    debugLog('Hiding upaya section for Landy-only report');
                }
            }
            
            // Default to upload
            const upayaScreenshotUploadFile = document.getElementById('upayaScreenshotUploadFile');
            if (upayaScreenshotUploadFile) upayaScreenshotUploadFile.checked = true;
        }
        
        const type = document.querySelector('input[name="upayaScreenshotType"]:checked')?.value || 'upload';
        const upayaScreenshotUploadFileGroup = document.getElementById('upayaScreenshotUploadFileGroup');
        const upayaScreenshotLinkWarning = document.getElementById('upayaScreenshotLinkWarning');
        
        if (upayaScreenshotUploadFileGroup) upayaScreenshotUploadFileGroup.classList.toggle('d-none', type !== 'upload');
        if (upayaScreenshotLinkWarning) upayaScreenshotLinkWarning.classList.toggle('d-none', type !== 'screenshot');
    }
    document.querySelectorAll('input[name="reportType[]"]').forEach(cb => {
        cb.addEventListener('change', toggleUpayaScreenshotInput);
    });
    document.querySelectorAll('input[name="upayaScreenshotType"]').forEach(r => {
        r.addEventListener('change', toggleUpayaScreenshotInput);
    });
    toggleUpayaScreenshotInput();

    function showAlert(msg, el) {
        let alertDiv = document.getElementById('formAlert');
        if (!alertDiv) {
            alertDiv = document.createElement('div');
            alertDiv.id = 'formAlert';
            alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `<span id="formAlertMsg"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
            const form = document.getElementById('wizardForm');
            if (el && el.parentNode) {
                el.parentNode.insertBefore(alertDiv, el);
            } else {
                form.parentNode.insertBefore(alertDiv, form);
            }
        }
        document.getElementById('formAlertMsg').innerText = msg;
        alertDiv.style.display = '';
        setTimeout(() => {
            if (alertDiv) alertDiv.classList.remove('show');
        }, 6000);
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // REMOVED: Form submission validation is now handled entirely by ajax-handler.js
    // This prevents double event listeners and conflicts
});
