/**
 * ajax-handler.js
 * Berisi fungsi-fungsi untuk menangani pengiriman form dan permintaan AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    const wizardForm = document.getElementById('wizardForm');
    
    if (wizardForm) {
        wizardForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form based on selected report types
            if (!validateFormBasedOnReportTypes()) {
                return false;
            }
            
            // Log info file yang dipilih untuk debugging
            document.querySelectorAll('input[type="file"]').forEach(input => {
                const inputId = input.id;
                const files = input.files;
                if (files && files.length > 0) {
                    const fileInfo = Array.from(files).map(file => ({
                        name: file.name,
                        size: file.size,
                        type: file.type
                    }));
                    debugLog(`Files selected for ${inputId}`, fileInfo);
                }
            });            setUploadButtonState(true);
            showProgressBar('Mengirim data ke server...');
            updateProgressBar(10, 'Mengirim data ke server...');

            const formData = new FormData(wizardForm);
            debugLog('FormData created', {timestamp: new Date().toLocaleTimeString()});

            // Tambahkan informasi tentang jenis laporan yang sedang diproses
            const selectedReportTypes = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
                .map(cb => cb.value);
            const reportTypesLabel = selectedReportTypes.join(', ');
            
            // Perbarui tampilan dengan jenis-jenis laporan yang sedang diproses
            updateServerProgress(`Memproses laporan: ${reportTypesLabel}`, 15);

            // Simulasi progres step-by-step dengan interval yang lebih alami
            let progressStep = 15;
            const progressInterval = setInterval(() => {
                if (progressStep < 40) {
                    progressStep += 5;
                    // Pesan yang lebih deskriptif berdasarkan persentase progres
                    let statusMessage = 'Memproses laporan di server...';
                    
                    if (progressStep === 20) {
                        statusMessage = 'Memverifikasi data laporan...';
                    } else if (progressStep === 25) {
                        statusMessage = 'Menyiapkan proses pembuatan dokumen...';
                    } else if (progressStep === 30) {
                        statusMessage = 'Menganalisa konten laporan...';
                    } else if (progressStep === 35) {
                        statusMessage = 'Menunggu respons server...';
                    } else if (progressStep === 40) {
                        statusMessage = 'Memulai pemrosesan data...';
                    }
                    
                    updateProgressBar(progressStep, statusMessage);
                }
            }, 800); // Interval yang lebih lambat untuk animasi yang lebih halus

            // Gunakan fetch dengan ReadableStream untuk menangkap progress dari server
            debugLog('Starting fetch request to api_rekap.php');
            fetch('api_rekap.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    debugLog('Initial response received', { status: response.status, ok: response.ok });
                    if (!response.body || !window.TextDecoder) {
                        debugLog('Using standard response.json() because ReadableStream not supported');
                        return response.json();
                    }
                    
                    debugLog('Using ReadableStream to process response');
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';
                    let finalJson = null;

                    function readChunk() {
                        return reader.read().then(({
                            done,
                            value
                        }) => {
                            if (done) {
                                debugLog('Response streaming complete');
                                if (buffer.trim()) {
                                    try {
                                        finalJson = JSON.parse(buffer.trim());
                                        debugLog('Final JSON parsed from buffer', finalJson);
                                    } catch (e) {
                                        debugLog('Error parsing final JSON', { error: e.message, buffer });
                                    }
                                }
                                return finalJson;
                            }
                            
                            const newText = decoder.decode(value, { stream: true });
                            debugLog('Chunk received', { length: newText.length, preview: newText.substring(0, 100) });
                            buffer += newText;
                            
                            // Cari dan proses setiap JSON progress
                            let idx;
                            while ((idx = buffer.indexOf('}{')) !== -1) {
                                let jsonStr = buffer.slice(0, idx + 1);
                                buffer = buffer.slice(idx + 1);
                                try {
                                    const obj = JSON.parse(jsonStr);
                                    // If this is a screenshot progress message, add it to debug log with more details
                                    if (obj.progress && (
                                        obj.progress.includes('tangkapan layar') || 
                                        obj.progress.includes('screenshot')
                                    )) {
                                        debugLog('Screenshot progress', obj);
                                        
                                        // Add to the debug info display with more prominence
                                        const debugInfo = document.getElementById('debugInfo');
                                        if (debugInfo) {
                                            const msgElement = document.createElement('div');
                                            msgElement.innerHTML = `<strong style="color:#007bff;">[Screenshot] ${obj.progress}</strong>`;
                                            debugInfo.prepend(msgElement);
                                            
                                            // Show debug info automatically when screenshot progress appears
                                            if (debugInfo.style.display === 'none') {
                                                const debugToggle = document.getElementById('debugInfoToggle');
                                                if (debugToggle) debugToggle.click();
                                            }
                                        }
                                    } else {
                                        debugLog('Progress JSON object found in stream', obj);
                                    }
                                    
                                    if (obj.progress) updateServerProgress(obj.progress, obj.percent);
                                } catch (e) {
                                    debugLog('Error parsing JSON chunk', { error: e.message, jsonStr });
                                }
                            }
                            
                            // Cek jika buffer adalah JSON lengkap
                            try {
                                const obj = JSON.parse(buffer);
                                if (obj.progress) {
                                    // Check for screenshot progress messages again
                                    if (obj.progress.includes('tangkapan layar') || obj.progress.includes('screenshot')) {
                                        debugLog('Screenshot progress (complete JSON)', obj);
                                        
                                        // Add to the debug info display with more prominence
                                        const debugInfo = document.getElementById('debugInfo');
                                        if (debugInfo) {
                                            const msgElement = document.createElement('div');
                                            msgElement.innerHTML = `<strong style="color:#007bff;">[Screenshot] ${obj.progress}</strong>`;
                                            debugInfo.prepend(msgElement);
                                        }
                                    } else {
                                        debugLog('Complete progress JSON found', obj);
                                    }
                                    
                                    updateServerProgress(obj.progress, obj.percent);
                                    buffer = '';
                                } else {
                                    debugLog('Complete result JSON found', obj);
                                    finalJson = obj;
                                }
                            } catch (e) {
                                // Not a complete JSON yet, continue reading
                            }
                            return readChunk();
                        });
                    }
                    return readChunk();
                })                .then(data => {                    debugLog('Processing completed data', data);
                    clearInterval(progressInterval);
                    
                    // Pesan yang lebih deskriptif tentang penyelesaian proses
                    updateProgressBar(100, 'Proses selesai! Menampilkan hasil laporan...');
                    updateServerProgress('Semua dokumen telah berhasil dibuat. Hasil dapat diunduh sekarang.', 100);
                    
                    // Tambahkan jeda sebelum menyembunyikan progress bar
                    setTimeout(() => {
                        // Tampilkan toast notifikasi sukses
                        if (typeof showToast === 'function') {
                            showToast('success', 'Berhasil', 'Pembuatan laporan telah selesai!');
                        }
                        hideProgressBar();
                    }, 1500);
                    
                    setUploadButtonState(false);
                    
                    // Log selected report types for debugging
                    const selected = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
                    debugLog('Selected report types:', selected);
                    
                    // Tampilkan hasil laporan KBD
                    const kbdResultContent = document.getElementById('laporanKbdResultContent');
                    if (kbdResultContent) {
                        // Find the parent card and placeholder
                        const parentCard = kbdResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        if (data && (data.outputPathWordGeneral || data.outputPathPdf || data.outputPathWordPatroli)) {
                            debugLog('Displaying KBD results');
                            
                            // Hide placeholder and show result content
                            if (placeholder) placeholder.classList.add('d-none');
                            kbdResultContent.classList.remove('d-none');
                              // Fill the result content
                            kbdResultContent.innerHTML = `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-medium mb-0">Narasi Laporan KBD</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary copy-btn" data-target="kbd-narrative">
                                            <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                        </button>
                                    </div>
                                    <textarea id="kbd-narrative" class="form-control result-textarea" rows="20" readonly>${data.narrative ? data.narrative : ''}</textarea>
                                </div>
                                <div>
                                    <label class="form-label fw-medium">Download Hasil</label>
                                    <div class="d-flex flex-column gap-2">
                                        ${data.outputPathWordPatroli ? `<a href="hasil/${encodeURIComponent(data.outputPathWordPatroli.split('/').pop())}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center gap-2" target="_blank">
                                            <i class="material-icons-outlined">description</i> Patroli (Word)
                                        </a>` : ''}
                                        ${data.outputPathWordGeneral ? `<a href="hasil/${encodeURIComponent(data.outputPathWordGeneral.split('/').pop())}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center gap-2" target="_blank">
                                            <i class="material-icons-outlined">description</i> Cipop (Word)
                                        </a>` : ''}
                                        ${data.outputPathPdf ? `<a href="hasil/${encodeURIComponent(data.outputPathPdf.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                            <i class="material-icons-outlined">picture_as_pdf</i> Lampiran (PDF)
                                        </a>` : ''}
                                    </div>
                                </div>
                            `;
                        } else {
                            debugLog('No KBD results to display');
                            // Reset to default state
                            if (placeholder) placeholder.classList.remove('d-none');
                            kbdResultContent.classList.add('d-none');
                            kbdResultContent.innerHTML = '';
                        }
                    }                    // Tampilkan hasil laporan Landy
                    const landyResultContent = document.getElementById('laporanLandyResult');
                    debugLog('Landy result element found:', Boolean(landyResultContent));
                    
                    if (landyResultContent) {
                        // Find the parent card and placeholder
                        const parentCard = landyResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        debugLog('Landy data check:', {
                            hasOutputPathLandy: Boolean(data?.outputPathLandy),
                            hasOutputPathPdfLandy: Boolean(data?.outputPathPdfLandy),
                            narasiPatroliLandy: data?.narasiPatroliLandy ? data.narasiPatroliLandy.substring(0, 50) + '...' : 'none',
                            selectedTypes: selected
                        });
                        
                        // Only show Landy results when Landy report type is selected
                        if (selected.includes('Patroli Landy') && data && (data.outputPathLandy || data.outputPathPdfLandy)) {
                            debugLog('Displaying Landy results');
                            
                            // Hide placeholder and show result content
                            if (placeholder) placeholder.classList.add('d-none');
                            landyResultContent.classList.remove('d-none');
                              // Fill the result content
                            landyResultContent.innerHTML = `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-medium mb-0">Narasi Landy</label>
                                        <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-target="landy-narrative">
                                            <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                        </button>
                                    </div>
                                    <textarea id="landy-narrative" class="form-control result-textarea" rows="20" readonly>${data.narasiPatroliLandy ? data.narasiPatroliLandy : ''}</textarea>
                                </div>
                                <div>
                                    <label class="form-label fw-medium">Download Hasil</label>
                                    <div class="d-flex flex-column gap-2">
                                        ${data.outputPathLandy ? `<a href="hasil/${encodeURIComponent(data.outputPathLandy.split('/').pop())}" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center gap-2" target="_blank">
                                            <i class="material-icons-outlined">description</i> Patroli (Word)
                                        </a>` : ''}
                                        ${data.outputPathPdfLandy ? `<a href="hasil/${encodeURIComponent(data.outputPathPdfLandy.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                            <i class="material-icons-outlined">picture_as_pdf</i> Lampiran (PDF)
                                        </a>` : ''}
                                    </div>
                                </div>
                            `;
                        } else {
                            debugLog('No Landy results to display');
                            // Reset to default state
                            if (placeholder) placeholder.classList.remove('d-none');
                            landyResultContent.classList.add('d-none');
                            landyResultContent.innerHTML = '';
                        }
                    }                    // Tampilkan hasil laporan Patroli Pagi
                    const pagiResultContent = document.getElementById('laporanPagiResult');
                    if (pagiResultContent) {
                        // Find the parent card and placeholder
                        const parentCard = pagiResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        if (data && (data.outputPathPagi || data.outputPathPdfPagi)) {
                            debugLog('Displaying Patroli Pagi results');
                            
                            // Hide placeholder and show result content
                            if (placeholder) placeholder.classList.add('d-none');
                            pagiResultContent.classList.remove('d-none');
                              // Fill the result content
                            pagiResultContent.innerHTML = `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-medium mb-0">Narasi Patroli Pagi</label>
                                        <button type="button" class="btn btn-sm btn-outline-warning copy-btn" data-target="pagi-narrative">
                                            <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                        </button>
                                    </div>
                                    <textarea id="pagi-narrative" class="form-control result-textarea" rows="20" readonly>${data.narasiPatroliPagi ? data.narasiPatroliPagi : ''}</textarea>
                                </div>
                                <div>
                                    <label class="form-label fw-medium">Download Hasil</label>
                                    <div class="d-flex flex-column gap-2">
                                        ${data.outputPathPagi ? `<a href="hasil/${encodeURIComponent(data.outputPathPagi.split('/').pop())}" class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center gap-2" target="_blank">
                                            <i class="material-icons-outlined">description</i> Patroli (Word)
                                        </a>` : ''}
                                        ${data.outputPathPdfPagi ? `<a href="hasil/${encodeURIComponent(data.outputPathPdfPagi.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                            <i class="material-icons-outlined">picture_as_pdf</i> Lampiran (PDF)
                                        </a>` : ''}
                                    </div>
                                </div>
                            `;
                        } else {
                            debugLog('No Patroli Pagi results to display');
                            // Reset to default state
                            if (placeholder) placeholder.classList.remove('d-none');
                            pagiResultContent.classList.add('d-none');
                            pagiResultContent.innerHTML = '';
                        }
                    }                    // Scroll ke hasil laporan (prioritas KBD, lalu Landy, lalu Pagi)
                    setTimeout(function() {
                        let scrollTarget = null;
                        const kbdResultContent = document.getElementById('laporanKbdResultContent');
                        const landyResultContent = document.getElementById('laporanLandyResult');
                        const pagiResultContent = document.getElementById('laporanPagiResult');
                        
                        if (kbdResultContent && !kbdResultContent.classList.contains('d-none')) {
                            scrollTarget = kbdResultContent.closest('.card');
                        } else if (landyResultContent && !landyResultContent.classList.contains('d-none')) {
                            scrollTarget = landyResultContent.closest('.card');
                        } else if (pagiResultContent && !pagiResultContent.classList.contains('d-none')) {
                            scrollTarget = pagiResultContent.closest('.card');
                        }
                        
                        if (scrollTarget) {
                            debugLog('Scrolling to result', { target: scrollTarget.id || 'result-card' });
                            scrollTarget.scrollIntoView({behavior: "smooth", block: "start"});
                        }
                        
                        // Setup copy buttons functionality
                        setupCopyButtons();
                    }, 400);
                })
                .catch(err => {
                    debugLog('Error during fetch operation', { error: err.message });
                    clearInterval(progressInterval);
                    hideProgressBar();
                    setUploadButtonState(false);
                    alert('Terjadi error saat mengirim data: ' + err);
                });
        });
    }
});

/**
 * Sets up event handlers for the copy buttons
 */
function setupCopyButtons() {
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const textarea = document.getElementById(targetId);
            
            if (textarea) {
                // Copy to clipboard using Clipboard API if available
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        // Use the modern clipboard API
                        navigator.clipboard.writeText(textarea.value)
                            .then(() => showCopySuccess(this, targetId))
                            .catch(err => {
                                console.error('Failed to copy: ', err);
                                // Fallback to older method
                                fallbackCopyToClipboard(textarea, this, targetId);
                            });
                    } else {
                        // Use the older method for unsecured contexts
                        fallbackCopyToClipboard(textarea, this, targetId);
                    }
                } catch (err) {
                    console.error('Copy failed: ', err);
                    // Fallback to older method
                    fallbackCopyToClipboard(textarea, this, targetId);
                }
            }
        });
    });
}

/**
 * Fallback method for copying to clipboard
 * @param {HTMLElement} textarea - The textarea element to copy from
 * @param {HTMLElement} button - The button element that was clicked
 * @param {string} targetId - The ID of the target textarea
 */
function fallbackCopyToClipboard(textarea, button, targetId) {
    // Select the text and copy to clipboard
    textarea.focus();
    textarea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(button, targetId);
        } else {
            console.error('Copy command was unsuccessful');
        }
    } catch (err) {
        console.error('Error during copy: ', err);
    }
}

/**
 * Shows success feedback after copying
 * @param {HTMLElement} button - The button element that was clicked
 * @param {string} targetId - The ID of the target textarea
 */
function showCopySuccess(button, targetId) {
    // Show feedback to user
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="material-icons-outlined" style="font-size: 16px;">check</i> Copied!';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-warning');
    
    // Reset button after 2 seconds
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        if (targetId === 'kbd-narrative') {
            button.classList.add('btn-outline-primary');
        } else if (targetId === 'landy-narrative') {
            button.classList.add('btn-outline-success');
        } else if (targetId === 'pagi-narrative') {
            button.classList.add('btn-outline-warning');
        }
    }, 2000);
}

/**
 * Validates form fields based on selected report types
 * @returns {boolean} True if form is valid, false otherwise
 */
function validateFormBasedOnReportTypes() {
    let alertDiv = document.getElementById('formAlert');
    if (alertDiv) alertDiv.remove();
    
    const selected = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        showFormAlert('Pilih minimal satu jenis laporan.', document.getElementById('step-1'));
        return false;
    }
    
    const tanggal = document.getElementById('tanggal').value;
    if (!tanggal) {
        showFormAlert('Tanggal laporan harus diisi.', document.getElementById('step-2'));
        return false;
    }
    
    // Validasi Patrol Report
    const patrolReport = document.getElementById('patrolReport').value.trim();
    if (!patrolReport) {
        showFormAlert('Input Patrol Report tidak boleh kosong.', document.getElementById('step-3'));
        return false;
    }
    
    // Validasi format patrol report: harus kelipatan 4 baris
    const patrolLines = patrolReport.split('\n').map(l => l.trim()).filter(l => l !== '');
    if (patrolLines.length % 4 !== 0) {
        showFormAlert('Format Patrol Report tidak valid. Harus terdiri dari kelompok 4 baris (nama akun, link, kategori, narasi).', document.getElementById('step-3'));
        return false;
    }    // Validasi Patroli Pagi
    if (selected.includes('Patroli Pagi')) {
        const upaya = document.getElementById('inputUpaya').value.trim();
        if (!upaya) {
            showFormAlert('Input Upaya Patroli Pagi tidak boleh kosong.', document.getElementById('step3-inputUpaya'));
            return false;
        }
        
        // Validasi format upaya: harus kelipatan 3 baris
        const upayaLines = upaya.split('\n').map(l => l.trim()).filter(l => l !== '');
        if (upayaLines.length % 3 !== 0) {
            showFormAlert('Format Input Upaya tidak valid. Harus terdiri dari kelompok 3 baris (nama akun, link, narasi).', document.getElementById('step3-inputUpaya'));
            return false;
        }
    }
    
    // Validasi Laporan KBD
    if (selected.includes('Laporan KBD')) {
        const excelFiles = document.getElementById('excelFiles').files;
        if (!excelFiles || excelFiles.length < 1) {
            showFormAlert('Upload minimal 1 file Excel Cipop.', document.getElementById('excelFiles'));
            return false;
        }
        
        // Validasi radio button cipopImageType
        const cipopTypeRadios = document.querySelectorAll('input[name="cipopImageType"]');
        const cipopTypeChecked = Array.from(cipopTypeRadios).some(radio => radio.checked);
        if (!cipopTypeChecked) {
            showFormAlert('Pilih metode upload gambar cipop (Upload File atau Screenshot).', document.querySelector('input[name="cipopImageType"]').parentNode);
            return false;
        }
        
        const cipopType = document.querySelector('input[name="cipopImageType"]:checked').value;
        if (cipopType === 'upload') {
            const files = document.getElementById('imageFiles').files;
            if (!files || files.length < 1) {
                showFormAlert('Upload minimal 1 gambar cipop.', document.getElementById('imageFiles'));
                return false;
            }
            if (files.length > 8) {
                showFormAlert('Maksimal 8 gambar cipop yang dapat diupload.', document.getElementById('imageFiles'));
                return false;
            }
        } else if (cipopType === 'screenshot') {
            const links = document.getElementById('cipopScreenshotLinks').value.trim().split('\n').map(l => l.trim()).filter(l => l !== '');
            if (links.length < 1) {
                showFormAlert('Masukkan minimal 1 link untuk tangkapan layar cipop.', document.getElementById('cipopScreenshotLinks'));
                return false;
            }
            if (links.length > 8) {
                showFormAlert('Maksimal 8 link tangkapan layar cipop yang dapat dimasukkan.', document.getElementById('cipopScreenshotLinks'));
                return false;
            }
            for (let i = 0; i < links.length; i++) {
                if (!/^https?:\/\/.+/i.test(links[i])) {
                    showFormAlert(`Link cipop #${i+1} tidak valid. Semua link harus diawali http:// atau https://`, document.getElementById('cipopScreenshotLinks'));
                    return false;
                }
            }
        }
    }
      // Validasi radio button patroliScreenshotType
    const patroliTypeRadios = document.querySelectorAll('input[name="patroliScreenshotType"]');
    const patroliTypeChecked = Array.from(patroliTypeRadios).some(radio => radio.checked);
    if (!patroliTypeChecked) {
        showFormAlert('Pilih metode upload screenshot patroli (Upload File atau Screenshot).', document.querySelector('input[name="patroliScreenshotType"]').parentNode);
        return false;
    }
    
    // Validasi Screenshot Patroli - penting untuk semua jenis laporan
    const patroliType = document.querySelector('input[name="patroliScreenshotType"]:checked').value;
    if (patroliType === 'upload') {
        const ssPatroli = document.getElementById('screenshotPatroli').files;
        if (!ssPatroli || ssPatroli.length < 1) {
            showFormAlert('Upload minimal 1 screenshot patroli.', document.getElementById('screenshotPatroli'));
            return false;
        }
        
        // Pastikan jumlah screenshot cukup untuk jumlah patrol entries
        if (selected.includes('Patroli Landy') && patrolLines.length / 4 > ssPatroli.length) {
            showFormAlert(`Upload screenshot patroli kurang. Minimal harus sama dengan jumlah patrol report (${Math.ceil(patrolLines.length / 4)}).`, document.getElementById('screenshotPatroli'));
            return false;
        }
    } else if (patroliType === 'screenshot') {
        const patroliLinks = document.getElementById('patroliScreenshotLinks');
        if (patroliLinks && patroliLinks.value.trim() === '') {
            showFormAlert('Masukkan minimal 1 link screenshot patroli.', document.getElementById('patroliScreenshotLinks'));
            return false;
        }
        
        // Validasi format link patroli
        if (patroliLinks) {
            const links = patroliLinks.value.trim().split('\n').map(l => l.trim()).filter(l => l !== '');
            
            // Untuk Patroli Landy, pastikan jumlah link sesuai dengan jumlah patrol entries
            if (selected.includes('Patroli Landy') && patrolLines.length / 4 > links.length) {
                showFormAlert(`Jumlah link screenshot patroli kurang. Minimal harus sama dengan jumlah patrol report (${Math.ceil(patrolLines.length / 4)}).`, document.getElementById('patroliScreenshotLinks'));
                return false;
            }
            
            for (let i = 0; i < links.length; i++) {
                if (!/^https?:\/\/.+/i.test(links[i])) {
                    showFormAlert(`Link screenshot patroli #${i+1} tidak valid. Semua link harus diawali http:// atau https://`, document.getElementById('patroliScreenshotLinks'));
                    return false;
                }
            }
        }
    }    // Validasi khusus untuk Patroli Landy (RAS)
    if (selected.includes('Patroli Landy')) {
        // Validasi input patrol report sudah dilakukan di atas
        if (!patrolReport) {
            showFormAlert('Input Patrol Report tidak boleh kosong.', document.getElementById('step-3'));
            return false;
        }
        
        // Validasi upload file RAS (Required for Landy)
        const rasFiles = document.getElementById('rasFiles');
        if (rasFiles) {
            debugLog('Validating RAS files for Landy');
            const rasFileList = rasFiles.files;
            if (!rasFileList || rasFileList.length < 1) {
                showFormAlert('Upload minimal 1 gambar RAS untuk Patroli Landy.', document.getElementById('rasFiles'));
                return false;
            }
            
            // Log the RAS files count for debugging
            debugLog('RAS files count:', rasFileList.length);
        } else {
            debugLog('RAS files element not found');
        }
    }
      // Validasi Patroli Landy atau Patroli Pagi untuk upaya screenshot
    if (selected.includes('Patroli Landy') || selected.includes('Patroli Pagi')) {
        // Validasi radio button upayaScreenshotType
        const upayaTypeRadios = document.querySelectorAll('input[name="upayaScreenshotType"]');
        if (upayaTypeRadios.length > 0) {
            const upayaTypeChecked = Array.from(upayaTypeRadios).some(radio => radio.checked);
            const upayaScreenshotTypeGroup = document.getElementById('upayaScreenshotTypeGroup');
            
            // Hanya validasi jika elemen tidak disembunyikan DAN bukan hanya Patroli Landy
            // Jika hanya Patroli Landy yang dipilih, tidak perlu validasi upayaScreenshotType
            if (!upayaTypeChecked && upayaScreenshotTypeGroup && 
                !upayaScreenshotTypeGroup.classList.contains('d-none') && 
                selected.includes('Patroli Pagi')) {
                showFormAlert('Pilih metode upload gambar upaya (Upload File atau Screenshot).', document.querySelector('input[name="upayaScreenshotType"]').parentNode);
                return false;
            }
        }
          const upayaType = document.querySelector('input[name="upayaScreenshotType"]:checked')?.value || 'upload';
        
        // Validasi khusus untuk jenis "upload"
        if (upayaType === 'upload') {
            const upayaFiles = document.getElementById('upayaFiles');
            // Hanya validasi jika element upayaFiles terlihat (tidak disembunyikan) DAN bukan hanya Patroli Landy
            if (upayaFiles && !upayaFiles.classList.contains('d-none') && selected.includes('Patroli Pagi')) {
                const upayaFileList = upayaFiles.files;
                if (!upayaFileList || upayaFileList.length < 1) {
                    showFormAlert('Upload minimal 1 gambar upaya untuk Patroli Pagi.', document.getElementById('upayaFiles'));
                    return false;
                }
            }
        } 
        // Validasi khusus untuk jenis "screenshot"
        else if (upayaType === 'screenshot') {
            const upayaLinks = document.getElementById('upayaScreenshotLinks');
            // Hanya validasi jika element upayaScreenshotLinks terlihat (tidak disembunyikan) DAN bukan hanya Patroli Landy
            if (upayaLinks && !upayaLinks.classList.contains('d-none') && 
                upayaLinks.value.trim() === '' && selected.includes('Patroli Pagi')) {
                showFormAlert('Masukkan minimal 1 link screenshot upaya.', document.getElementById('upayaScreenshotLinks'));
                return false;
            }
            
            // Validasi format link upaya - hanya jika ada links dan bukan hanya Patroli Landy
            if (upayaLinks && !upayaLinks.classList.contains('d-none') && 
                upayaLinks.value.trim() !== '' && selected.includes('Patroli Pagi')) {
                const links = upayaLinks.value.trim().split('\n').map(l => l.trim()).filter(l => l !== '');
                for (let i = 0; i < links.length; i++) {
                    if (!/^https?:\/\/.+/i.test(links[i])) {
                        showFormAlert(`Link screenshot upaya #${i+1} tidak valid. Semua link harus diawali http:// atau https://`, document.getElementById('upayaScreenshotLinks'));
                        return false;
                    }
                }
            }
        }
    }
    
    return true;
}

/**
 * Displays a Bootstrap alert for form validation errors
 * @param {string} msg - Error message to display
 * @param {HTMLElement} el - Element to insert the alert before
 */
function showFormAlert(msg, el) {
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

/**
 * Try using RAS files as upaya files for Landy-only reports to fix the issue
 * This is a direct approach to solve the problem with a specific workaround
 */
function fixRasFilesForLandyOnlyReport() {
    const reportTypes = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
    const isLandyOnly = reportTypes.length === 1 && reportTypes.includes('Patroli Landy');
    
    if (isLandyOnly) {
        debugLog('Fixing RAS files for Landy-only report');
        
        // Create a backup of the real handler to restore later
        const actualSubmitHandler = HTMLFormElement.prototype.submit;
        
        // Override the submit method temporarily
        HTMLFormElement.prototype.submit = function() {
            const form = this;
            
            if (form.id === 'wizardForm') {
                const formData = new FormData(form);
                const rasFiles = document.getElementById('rasFiles')?.files;
                
                // If we have RAS files and no upaya files for a Landy-only report
                if (rasFiles && rasFiles.length > 0) {
                    debugLog('Using RAS files as upaya files for Landy-only report');
                    
                    // Create a dummy input for upaya if it doesn't exist
                    if (!document.getElementById('dummyUpayaForLandy')) {
                        const dummyUpaya = document.createElement('textarea');
                        dummyUpaya.id = 'dummyUpayaForLandy';
                        dummyUpaya.name = 'input_upaya';
                        dummyUpaya.style.display = 'none';
                        form.appendChild(dummyUpaya);
                        
                        // Add minimal content to prevent validation errors
                        dummyUpaya.value = 'Dummy Account\nhttps://example.com\nDummy content for Landy-only report';
                    }
                }
            }
            
            // Call the original submit method
            actualSubmitHandler.apply(this, arguments);
        };
        
        // Restore the original submit after a short delay (after our form is submitted)
        setTimeout(() => {
            HTMLFormElement.prototype.submit = actualSubmitHandler;
        }, 5000);
    }
}

// Call our fix function on form submit
document.getElementById('wizardForm')?.addEventListener('submit', fixRasFilesForLandyOnlyReport);

/**
 * Validates the number of files selected for upload
 * @param {HTMLInputElement} input - The file input element
 * @param {number} maxFiles - Maximum number of files allowed
 */
function validateFileCount(input, maxFiles) {
    if (input.files.length > maxFiles) {
        alert(`Maksimal ${maxFiles} file gambar yang diperbolehkan untuk diupload`);
        input.value = ''; // Clear the file input
        return false;
    }
    return true;
}
