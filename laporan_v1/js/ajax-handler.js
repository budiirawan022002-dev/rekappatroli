/**
 * ajax-handler.js
 * Berisi fungsi-fungsi untuk menangani pengiriman form dan permintaan AJAX
 */

function getHasilFileName(filePath) {
    if (!filePath) {
        return '';
    }
    const parts = String(filePath).split(/[/\\]/);
    return parts[parts.length - 1] || '';
}

// Add global error handler for debugging
window.addEventListener('error', function(e) {
    console.error('JavaScript Runtime Error:', {
        message: e.message,
        filename: e.filename,
        lineno: e.lineno,
        colno: e.colno,
        error: e.error
    });
});

// Add unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('AJAX Handler loaded - Laporan Khusus support enabled');
    
    // Test if required elements exist
    const wizardForm = document.getElementById('wizardForm');
    const progressOverlay = document.getElementById('progressOverlay');
    const progressBar = document.getElementById('progressBar');
    const progressBarStatus = document.getElementById('progressBarStatus');
    
    console.log('Required elements check:', {
        wizardForm: Boolean(wizardForm),
        progressOverlay: Boolean(progressOverlay),
        progressBar: Boolean(progressBar),
        progressBarStatus: Boolean(progressBarStatus)
    });
    
    console.log('Wizard form element found:', Boolean(wizardForm));
    
    if (!wizardForm) {
        console.error('❌ CRITICAL: wizardForm not found! AJAX handler cannot be attached.');
        return;
    }
    
    console.log('✅ Attaching submit event listener to wizard form');
    
    // Use capture phase to intercept BEFORE any other handlers
    wizardForm.addEventListener('submit', function(e) {
        console.log('🚀 Form submit event triggered in AJAX handler!');
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('🚀 === FORM SUBMISSION START ===');
        console.log('Starting form submission process...');
        
        // Log selected report types FIRST
        const selectedReportsDebug = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
            .map(cb => cb.value);
        console.log('📋 Selected Report Types:', selectedReportsDebug);
        
        // Test progress bar immediately when form is submitted
        console.log('Testing progress bar functionality...');
        try {
            if (typeof showProgressBar !== 'function') {
                console.error('showProgressBar function not found!');
                return false;
            }
            
            if (typeof updateProgressBar !== 'function') {
                console.error('updateProgressBar function not found!');
                return false;
            }
            
            showProgressBar('Testing...');
            updateProgressBar(25, 'Form submitted, testing progress...');
            console.log('Progress bar test completed');
        } catch (error) {
            console.error('Error testing progress bar:', error);
            alert('Error testing progress bar: ' + error.message);
            return false;
        }
        
        // Validate form based on selected report types
        console.log('Starting form validation...');
        let validationResult;
        try {
            validationResult = validateFormBasedOnReportTypes();
            console.log('Validation result:', validationResult);
            console.log('Validation result type:', typeof validationResult);
        } catch (error) {
            console.error('Error during validation:', error);
            hideProgressBar();
            alert('Error during form validation: ' + error.message);
            return false;
        }
        
        if (validationResult !== true) {
            console.log('Form validation failed, stopping submission. Result was:', validationResult);
            hideProgressBar();
            return false;
        }
        
        console.log('Form validation passed, proceeding with submission...');
        
        // Get selected report types for debugging
        const debugSelectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
            .map(cb => cb.value);
        
        // Special debug for Laporan Khusus
        if (debugSelectedReports.includes('Laporan Khusus')) {
            debugLog('=== LAPORAN KHUSUS PROCESSING START ===');
            debugLog('Selected report types', debugSelectedReports);
            const tema = document.getElementById('inputTema')?.value || '';
            const patrolReportKhusus = document.getElementById('patrolReportKhusus')?.value || '';
            debugLog('Laporan Khusus data', {
                tema: tema,
                patrolReportLength: patrolReportKhusus.length,
                hasTema: Boolean(tema),
                hasPatrolReport: Boolean(patrolReportKhusus)
            });
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
        });
        
        console.log('About to set upload button state and show progress bar...');
        setUploadButtonState(true);
        console.log('Upload button state set to disabled');
        
        showProgressBar('Mengirim data ke server...');
        console.log('Progress bar should be visible now');
        
        updateProgressBar(10, 'Mengirim data ke server...');
        console.log('Progress bar updated to 10%');

        const formData = new FormData(wizardForm);
        
        // CRITICAL: Debug FormData before sending
        console.log('=== FORMDATA CONTENTS BEFORE SEND ===');
        const formDataEntries = {};
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                formDataEntries[key] = `File: ${value.name} (${value.size} bytes)`;
            } else {
                formDataEntries[key] = value;
            }
        }
        console.table(formDataEntries);
        
        // CRITICAL: Verify reportType exists
        const reportTypes = formData.getAll('reportType[]');
        console.log('reportType[] count:', reportTypes.length);
        console.log('reportType[] values:', reportTypes);
        
        if (reportTypes.length === 0) {
            console.error('❌ FATAL: reportType[] is empty!');
            clearInterval(progressInterval);
            hideProgressBar();
            alert('ERROR KRITIS: Jenis laporan tidak terdeteksi!\n\nSilakan:\n1. Refresh halaman (Ctrl+F5)\n2. Pilih jenis laporan lagi\n3. Coba lagi');
            setUploadButtonState(false);
            return false;
        }
        
        debugLog('FormData created', {
            timestamp: new Date().toLocaleTimeString(),
            reportTypes: reportTypes
        });

            // Tambahkan informasi tentang jenis laporan yang sedang diproses
            const selectedReportTypes = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
                .map(cb => cb.value);
            const reportTypesLabel = selectedReportTypes.join(', ');
            
            // Special debug for Laporan Khusus submission
            if (selectedReportTypes.includes('Laporan Khusus')) {
                debugLog('=== SUBMITTING LAPORAN KHUSUS ===', {
                    selectedReports: selectedReportTypes,
                    tema: formData.get('input_tema'),
                    patrolReportKhusus: formData.get('patrolReportKhusus') ? 'Present' : 'Missing',
                    excelFilesKhusus: formData.get('excelFilesKhusus') ? 'Present' : 'Missing'
                });
            }
            
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
            
            // Debug FormData contents for Laporan Khusus
            if (selectedReportTypes.includes('Laporan Khusus')) {
                debugLog('=== FORMDATA DEBUG FOR LAPORAN KHUSUS ===');
                const formDataEntries = [];
                for (let [key, value] of formData.entries()) {
                    if (typeof value === 'object' && value.constructor === File) {
                        formDataEntries.push([key, `File: ${value.name} (${value.size} bytes)`]);
                    } else {
                        formDataEntries.push([key, value]);
                    }
                }
                debugLog('FormData entries', formDataEntries);
                
                // Extra debug for Laporan Khusus specific fields
                console.log('=== LAPORAN KHUSUS FORM DATA VALIDATION ===');
                console.log('reportType[]:', formData.getAll('reportType[]'));
                console.log('tanggal:', formData.get('tanggal'));
                console.log('input_tema:', formData.get('input_tema'));
                console.log('patrolReportKhusus:', formData.get('patrolReportKhusus') ? 'Present (length: ' + formData.get('patrolReportKhusus').length + ')' : 'Missing');
                console.log('excelFilesKhusus:', formData.get('excelFilesKhusus') ? 'Present (name: ' + formData.get('excelFilesKhusus').name + ')' : 'Missing');
                console.log('cipopImageTypeKhusus:', formData.get('cipopImageTypeKhusus'));
                console.log('patroliScreenshotTypeKhusus:', formData.get('patroliScreenshotTypeKhusus'));
                
                // Check for conditional fields based on radio selections
                const cipopType = formData.get('cipopImageTypeKhusus');
                const patroliType = formData.get('patroliScreenshotTypeKhusus');
                
                if (cipopType === 'upload') {
                    console.log('imageFilesKhusus:', formData.get('imageFilesKhusus') ? 'Present' : 'Missing');
                } else if (cipopType === 'screenshot') {
                    console.log('cipopScreenshotLinksKhusus:', formData.get('cipopScreenshotLinksKhusus') ? 'Present' : 'Missing');
                }
                
                if (patroliType === 'upload') {
                    console.log('screenshotPatroliKhusus:', formData.get('screenshotPatroliKhusus') ? 'Present' : 'Missing');
                } else if (patroliType === 'screenshot') {
                    console.log('patroliScreenshotLinksKhusus:', formData.get('patroliScreenshotLinksKhusus') ? 'Present' : 'Missing');
                }
            }
            
            console.log('🚀 Sending fetch request to api_rekap.php...');
            
            fetch('api_rekap.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin' // Include cookies/session
                })
                .then(response => {
                    console.log('📥 Response received:', {
                        status: response.status,
                        statusText: response.statusText,
                        headers: {
                            contentType: response.headers.get('content-type')
                        }
                    });
                    
                    // Check if response is OK
                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                    }
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('❌ Response is not JSON! Content-Type:', contentType);
                        // Try to get response text for debugging
                        return response.text().then(text => {
                            console.error('Response body (first 500 chars):', text.substring(0, 500));
                            throw new Error('Server tidak mengembalikan JSON. Kemungkinan ada PHP error.');
                        });
                    }
                    
                    return response;
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
                                    
                                    // Special debug for Laporan Khusus progress
                                    if (obj.progress && obj.progress.toLowerCase().includes('khusus')) {
                                        debugLog('=== LAPORAN KHUSUS PROGRESS ===', obj);
                                        
                                        // Add to the debug info display with more prominence
                                        const debugInfo = document.getElementById('debugInfo');
                                        if (debugInfo) {
                                            const msgElement = document.createElement('div');
                                            msgElement.innerHTML = `<strong style="color:#dc3545;">[LAPORAN KHUSUS] ${obj.progress}</strong> (${obj.percent}%)`;
                                            debugInfo.prepend(msgElement);
                                            
                                            // Show debug info automatically when laporan khusus progress appears
                                            if (debugInfo.style.display === 'none') {
                                                const debugToggle = document.getElementById('debugInfoToggle');
                                                if (debugToggle) debugToggle.click();
                                            }
                                        }
                                    }
                                    // If this is a screenshot progress message, add it to debug log with more details
                                    else if (obj.progress && (
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
                                    // Special debug for Laporan Khusus progress (complete JSON)
                                    if (obj.progress.toLowerCase().includes('khusus')) {
                                        debugLog('=== LAPORAN KHUSUS PROGRESS (Complete) ===', obj);
                                        
                                        // Add to the debug info display with more prominence
                                        const debugInfo = document.getElementById('debugInfo');
                                        if (debugInfo) {
                                            const msgElement = document.createElement('div');
                                            msgElement.innerHTML = `<strong style="color:#dc3545;">[LAPORAN KHUSUS] ${obj.progress}</strong> (${obj.percent}%)`;
                                            debugInfo.prepend(msgElement);
                                        }
                                    }
                                    // Check for screenshot progress messages again
                                    else if (obj.progress.includes('tangkapan layar') || obj.progress.includes('screenshot')) {
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
                })
                .then(data => {
                    console.log('🎯 ========== PROCESSING RESPONSE DATA ==========');
                    console.log('Data exists:', Boolean(data));
                    console.log('Data type:', typeof data);
                    
                    if (!data) {
                        console.error('❌ FATAL: Response data is null/undefined!');
                        throw new Error('Server response is empty');
                    }
                    
                    console.log('All response keys:', Object.keys(data));
                    console.log('Full response data:', data);
                    
                    // Check for error in response
                    if (data.success === false) {
                        console.error('❌ Server returned error:', data.message);
                        throw new Error(data.message || 'Server processing failed');
                    }
                    
                    debugLog('Processing completed data', data);
                    
                    // Debug info from API
                    if (data && data.debug) {
                        console.log('=== API DEBUG INFO ===', data.debug);
                        console.log('Expected Fields:', data.debug.expectedFields);
                        console.log('Is Patroli Landy:', data.debug.isPatroliLandy);
                        console.log('Platform Counts:', data.debug.platformCounts);
                        console.log('Raw Report Length:', data.debug.rawReportLength);
                        console.log('First Report:', data.debug.firstReport);
                    }
                    
                    // Special debug for Laporan Khusus response
                    if (data && (data.outputPathWordGeneralKhusus || data.outputPathPdfKhusus || data.outputPathWordPatroliKhusus || data.narrativeKhusus || data.fullNarrativeKhusus)) {
                        debugLog('=== LAPORAN KHUSUS RESPONSE DATA ===', {
                            hasOutputPathWordGeneralKhusus: Boolean(data?.outputPathWordGeneralKhusus),
                            hasOutputPathPdfKhusus: Boolean(data?.outputPathPdfKhusus),
                            hasOutputPathWordPatroliKhusus: Boolean(data?.outputPathWordPatroliKhusus),
                            hasNarrativeKhusus: Boolean(data?.narrativeKhusus || data?.fullNarrativeKhusus),
                            outputPathWordGeneralKhususValue: data?.outputPathWordGeneralKhusus,
                            outputPathPdfKhususValue: data?.outputPathPdfKhusus,
                            outputPathWordPatroliKhususValue: data?.outputPathWordPatroliKhusus,
                            narrativeKhususLength: data?.fullNarrativeKhusus ? data.fullNarrativeKhusus.length : (data?.narrativeKhusus ? data.narrativeKhusus.length : 0)
                        });
                    }
                    
                    debugLog('Full response data object:', {
                        success: data?.success,
                        hasNarrative: Boolean(data?.narrative),
                        hasOutputPathWordGeneral: Boolean(data?.outputPathWordGeneral),
                        hasOutputPathPdf: Boolean(data?.outputPathPdf),
                        hasOutputPathWordPatroli: Boolean(data?.outputPathWordPatroli),
                        hasOutputPathLandy: Boolean(data?.outputPathLandy),
                        hasOutputPathPdfLandy: Boolean(data?.outputPathPdfLandy),
                        hasOutputPathPagi: Boolean(data?.outputPathPagi),
                        hasOutputPathPdfPagi: Boolean(data?.outputPathPdfPagi),
                        hasNarasiPatroliLandy: Boolean(data?.narasiPatroliLandy),
                        hasNarasiPatroliPagi: Boolean(data?.narasiPatroliPagi),
                        // Laporan Khusus fields
                        hasOutputPathWordGeneralKhusus: Boolean(data?.outputPathWordGeneralKhusus),
                        hasOutputPathPdfKhusus: Boolean(data?.outputPathPdfKhusus),
                        hasOutputPathWordPatroliKhusus: Boolean(data?.outputPathWordPatroliKhusus),
                        hasNarrativeKhusus: Boolean(data?.narrativeKhusus || data?.fullNarrativeKhusus),
                        outputPathLandyValue: data?.outputPathLandy,
                        outputPathPdfLandyValue: data?.outputPathPdfLandy,
                        outputPathPagiValue: data?.outputPathPagi,
                        outputPathPdfPagiValue: data?.outputPathPdfPagi,
                        narasiLandyLength: data?.narasiPatroliLandy ? data.narasiPatroliLandy.length : 0,
                        narasiPagiLength: data?.narasiPatroliPagi ? data.narasiPatroliPagi.length : 0,
                        fullDataKeys: data ? Object.keys(data) : [],
                        dataAsString: JSON.stringify(data).substring(0, 500) + '...'
                    });
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
                        
                        // Clear form data from localStorage after successful submission
                        if (typeof clearFormData === 'function') {
                            clearFormData();
                            console.log('✅ Form data cleared after successful submission');
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
                        console.log('📋 === LAPORAN KBD DISPLAY ===');
                        console.log('KBD selected in reportTypes:', selected.includes('Laporan KBD'));
                        console.log('Checking KBD data fields:');
                        console.log('  - data exists:', Boolean(data));
                        console.log('  - outputPathWordGeneral:', data?.outputPathWordGeneral || 'MISSING');
                        console.log('  - outputPathPdf:', data?.outputPathPdf || 'MISSING');
                        console.log('  - outputPathWordPatroli:', data?.outputPathWordPatroli || 'MISSING');
                        console.log('  - narrative:', data?.narrative ? `${data.narrative.length} chars` : 'MISSING');
                        
                        // Find the parent card and placeholder
                        const parentCard = kbdResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        const hasKbdData = data && (data.outputPathWordGeneral || data.outputPathPdf || data.outputPathWordPatroli || data.narrative);
                        console.log('Has KBD data:', hasKbdData);
                        
                        if (hasKbdData) {
                            console.log('✅ Displaying KBD results');
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
                                    <textarea id="kbd-narrative" class="form-control result-textarea" rows="20">${data.narrative ? data.narrative : ''}</textarea>
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
                    }
                    
                    // Tampilkan hasil laporan Khusus
                    const khususResultContent = document.getElementById('laporanKhususResultContent');
                    console.log('=== LAPORAN KHUSUS RESULT PROCESSING ===');
                    console.log('khususResultContent element found:', Boolean(khususResultContent));
                    
                    if (khususResultContent) {
                        // Find the parent card and placeholder
                        const parentCard = khususResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        console.log('parentCard found:', Boolean(parentCard));
                        console.log('placeholder found:', Boolean(placeholder));
                        
                        // Get selected report types
                        const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
                        console.log('selectedReports:', selectedReports);
                        console.log('includes Laporan Khusus:', selectedReports.includes('Laporan Khusus'));
                        
                        // Check if this is Laporan Khusus request
                        if (selectedReports.includes('Laporan Khusus')) {
                            console.log('Processing Laporan Khusus results...');
                            
                            // Check what data we have
                            const hasAnyKhususData = data && (
                                data.outputPathWordGeneralKhusus || 
                                data.outputPathPdfKhusus || 
                                data.outputPathWordPatroliKhusus || 
                                data.fullNarrativeKhusus || data.narrativeKhusus
                            );
                            
                            console.log('hasAnyKhususData:', hasAnyKhususData);
                            console.log('data exists:', Boolean(data));
                            
                            if (data) {
                                console.log('Available Khusus fields:', {
                                    outputPathWordGeneralKhusus: data.outputPathWordGeneralKhusus || 'missing',
                                    outputPathPdfKhusus: data.outputPathPdfKhusus || 'missing',
                                    outputPathWordPatroliKhusus: data.outputPathWordPatroliKhusus || 'missing',
                                    narrativeKhusus: data.narrativeKhusus ? `${data.narrativeKhusus.length} chars` : 'missing',
                                    fullNarrativeKhusus: data.fullNarrativeKhusus ? `${data.fullNarrativeKhusus.length} chars` : 'missing'
                                });
                                
                                // Debug: Log ALL data keys to see what the server actually returned
                                console.log('=== ALL SERVER DATA KEYS ===', Object.keys(data));
                                console.log('=== FULL SERVER DATA ===', data);
                                
                                // Check if data has success field
                                console.log('Response success field:', data.success);
                                console.log('Response message field:', data.message || 'no message');
                                
                                // Check if there are any error fields
                                if (data.error) {
                                    console.log('Response error field:', data.error);
                                }
                            }
                            
                            if (hasAnyKhususData) {
                                debugLog('Displaying Laporan Khusus results');
                                console.log('Showing Laporan Khusus results with data');
                                
                                // Hide placeholder and show result content
                                if (placeholder) {
                                    placeholder.classList.add('d-none');
                                    console.log('Placeholder hidden');
                                }
                                khususResultContent.classList.remove('d-none');
                                console.log('Result content shown');
                                
                                // Fill the result content
                                khususResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Laporan Khusus</label>
                                            <button type="button" class="btn btn-sm btn-outline-primary copy-btn" data-target="khusus-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="khusus-narrative" class="form-control result-textarea" rows="20">${data.fullNarrativeKhusus || data.narrativeKhusus || 'Narasi tidak tersedia'}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="d-flex flex-column gap-2">
                                            ${data.outputPathWordPatroliKhusus ? `<a href="hasil/${encodeURIComponent(data.outputPathWordPatroliKhusus.split('/').pop())}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">description</i> Patroli (Word)
                                            </a>` : '<div class="alert alert-warning small">File Patroli Word tidak tersedia</div>'}
                                            ${data.outputPathWordGeneralKhusus ? `<a href="hasil/${encodeURIComponent(data.outputPathWordGeneralKhusus.split('/').pop())}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">description</i> Cipop (Word)
                                            </a>` : '<div class="alert alert-warning small">File Cipop Word tidak tersedia</div>'}
                                            ${data.outputPathPdfKhusus ? `<a href="hasil/${encodeURIComponent(data.outputPathPdfKhusus.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">picture_as_pdf</i> Lampiran (PDF)
                                            </a>` : '<div class="alert alert-warning small">File PDF tidak tersedia</div>'}
                                        </div>
                                    </div>
                                `;
                                console.log('Result content HTML set');
                            } else {
                                console.log('No Khusus data available, showing error state');
                                debugLog('No Laporan Khusus data available');
                                
                                // Hide placeholder and show error
                                if (placeholder) placeholder.classList.add('d-none');
                                khususResultContent.classList.remove('d-none');
                                
                                khususResultContent.innerHTML = `
                                    <div class="alert alert-danger">
                                        <i class="material-icons-outlined">error</i> 
                                        Pemrosesan Laporan Khusus gagal atau tidak ada data yang dihasilkan.
                                        <details class="mt-2">
                                            <summary>Debug Info</summary>
                                            <pre class="mt-2 small">${JSON.stringify({
                                                dataExists: Boolean(data),
                                                responseSuccess: data?.success,
                                                responseMessage: data?.message,
                                                responseError: data?.error,
                                                allDataKeys: data ? Object.keys(data) : [],
                                                fullResponseData: data,
                                                khususFields: {
                                                    outputPathWordGeneralKhusus: data?.outputPathWordGeneralKhusus || 'missing',
                                                    outputPathPdfKhusus: data?.outputPathPdfKhusus || 'missing',
                                                    outputPathWordPatroliKhusus: data?.outputPathWordPatroliKhusus || 'missing',
                                                    narrativeKhusus: data?.narrativeKhusus ? 'present' : 'missing',
                                                    fullNarrativeKhusus: data?.fullNarrativeKhusus ? 'present' : 'missing'
                                                }
                                            }, null, 2)}</pre>
                                        </details>
                                    </div>
                                `;
                            }
                        } else {
                            debugLog('No Laporan Khusus results to display - not selected');
                            console.log('Laporan Khusus not selected, hiding results');
                            // Reset to default state
                            if (placeholder) placeholder.classList.remove('d-none');
                            khususResultContent.classList.add('d-none');
                            khususResultContent.innerHTML = '';
                        }
                    } else {
                        console.error('laporanKhususResultContent element not found!');
                    }

                    // Tampilkan hasil laporan Landy
                    const landyResultContent = document.getElementById('laporanLandyResult');
                    debugLog('Landy result element found:', Boolean(landyResultContent));
                    
                    if (landyResultContent) {
                        // Find the parent card and placeholder
                        const parentCard = landyResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        debugLog('Landy data check:', {
                            hasOutputPathLandy: Boolean(data?.outputPathLandy),
                            hasOutputPathPdfLandy: Boolean(data?.outputPathPdfLandy),
                            outputPathLandyValue: data?.outputPathLandy,
                            outputPathPdfLandyValue: data?.outputPathPdfLandy,
                            narasiPatroliLandy: data?.narasiPatroliLandy ? data.narasiPatroliLandy.substring(0, 50) + '...' : 'none',
                            hasNarasiPatroliLandy: Boolean(data?.narasiPatroliLandy),
                            narasiLength: data?.narasiPatroliLandy ? data.narasiPatroliLandy.length : 0,
                            selectedTypes: selected,
                            selectedIncludesLandy: selected.includes('Patroli Landy'),
                            dataExists: Boolean(data),
                            conditionCheck: {
                                hasOutputPath: Boolean(data?.outputPathLandy || data?.outputPathPdfLandy),
                                hasNarasi: Boolean(data?.narasiPatroliLandy),
                                anyContent: Boolean(data?.outputPathLandy || data?.outputPathPdfLandy || data?.narasiPatroliLandy)
                            }
                        });
                        
                        // Only show Landy results when Landy report type is selected
                        if (selected.includes('Patroli Landy')) {
                            debugLog('Landy report type is selected, forcing display for debugging...');
                            
                            // Always show the section when Landy is selected for debugging
                            if (placeholder) placeholder.classList.add('d-none');
                            landyResultContent.classList.remove('d-none');
                            
                            if (data && (data.outputPathLandy || data.outputPathPdfLandy)) {
                                debugLog('Displaying Landy results - files available');
                                
                                landyResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli MBG dan Sore</label>
                                            <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-target="landy-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="landy-narrative" class="form-control result-textarea" rows="20">${data.narasiPatroliLandy ? data.narasiPatroliLandy : ''}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="d-flex flex-column gap-2">
                                            ${data.outputPathLandy ? `<a href="hasil/${encodeURIComponent(data.outputPathLandy.split('/').pop())}" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">description</i> Patroli (Word)
                                            </a>` : '<div class="alert alert-warning small">File Word tidak tersedia</div>'}
                                            ${data.outputPathPdfLandy ? `<a href="hasil/${encodeURIComponent(data.outputPathPdfLandy.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">picture_as_pdf</i> Lampiran (PDF)
                                            </a>` : '<div class="alert alert-warning small">File PDF tidak tersedia</div>'}
                                        </div>
                                    </div>
                                `;
                            } else if (data && data.narasiPatroliLandy) {
                                debugLog('Displaying Landy results - narasi only');
                                
                                landyResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli MBG dan Sore</label>
                                            <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-target="landy-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="landy-narrative" class="form-control result-textarea" rows="20">${data.narasiPatroliLandy}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="alert alert-warning">
                                            <i class="material-icons-outlined">warning</i> 
                                            File laporan Patroli MBG dan Sore gagal dibuat, tapi narasi tersedia.
                                        </div>
                                    </div>
                                `;
                            } else {
                                debugLog('Displaying Landy results - error state or no data');
                                
                                landyResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli MBG dan Sore</label>
                                        </div>
                                        <textarea id="landy-narrative" class="form-control result-textarea" rows="10">${data?.narasiPatroliLandy || 'Narasi tidak tersedia atau pemrosesan gagal'}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="alert alert-danger">
                                            <i class="material-icons-outlined">error</i> 
                                            Pemrosesan laporan Patroli MBG dan Sore gagal. 
                                            <details class="mt-2">
                                                <summary>Debug Info</summary>
                                                <pre class="mt-2 small">${JSON.stringify({
                                                    dataExists: Boolean(data),
                                                    hasOutputPathLandy: Boolean(data?.outputPathLandy),
                                                    hasOutputPathPdfLandy: Boolean(data?.outputPathPdfLandy),
                                                    hasNarasiPatroliLandy: Boolean(data?.narasiPatroliLandy),
                                                    outputPathLandyValue: data?.outputPathLandy || 'empty',
                                                    outputPathPdfLandyValue: data?.outputPathPdfLandy || 'empty'
                                                }, null, 2)}</pre>
                                            </details>
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            debugLog('No Landy results to display - Detailed reason:', {
                                selectedIncludesLandy: selected.includes('Patroli Landy'),
                                dataExists: Boolean(data),
                                hasOutputPathLandy: Boolean(data?.outputPathLandy),
                                hasOutputPathPdfLandy: Boolean(data?.outputPathPdfLandy),
                                outputPathLandyValue: data?.outputPathLandy,
                                outputPathPdfLandyValue: data?.outputPathPdfLandy,
                                fullCondition: selected.includes('Patroli Landy') && data && (data.outputPathLandy || data.outputPathPdfLandy)
                            });
                            // Reset to default state
                            if (placeholder) placeholder.classList.remove('d-none');
                            landyResultContent.classList.add('d-none');
                            landyResultContent.innerHTML = '';
                        }
                    }

                    // Tampilkan hasil Laporan MBG Lengkap
                    const mbgLengkapResultContent = document.getElementById('laporanMbgLengkapResult');
                    if (mbgLengkapResultContent) {
                        const parentCard = mbgLengkapResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;

                        if (selected.includes('Laporan MBG Lengkap')) {
                            if (placeholder) placeholder.classList.add('d-none');
                            mbgLengkapResultContent.classList.remove('d-none');

                            const mbgExcelFile = data.outputFilenameExcelMbgLengkap || getHasilFileName(data.outputPathExcelMbgLengkap);
                            if (data && (data.outputPathMbgLengkap || data.outputPathPdfMbgLengkap || data.outputPathPdfCipopMbg || data.outputPathExcelMbgLengkap || mbgExcelFile || data.narasiMbgLengkap || data.narasiMbgLengkapAmplifikasi)) {
                                mbgLengkapResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Laporan MBG Lengkap (Patroli + Amplifikasi)</label>
                                            <button type="button" class="btn btn-sm btn-outline-success copy-btn" data-target="mbg-lengkap-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="mbg-lengkap-narrative" class="form-control result-textarea" rows="20">${data.narasiMbgLengkap || ''}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Kasatgas MBG (Patroli + Amplifikasi)</label>
                                            <button type="button" class="btn btn-sm btn-outline-primary copy-btn" data-target="mbg-lengkap-amplifikasi-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="mbg-lengkap-amplifikasi-narrative" class="form-control result-textarea" rows="22">${data.narasiMbgLengkapAmplifikasi || ''}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="d-flex flex-column gap-2">
                                            ${data.outputPathMbgLengkap ? `<a href="hasil/${encodeURIComponent(getHasilFileName(data.outputPathMbgLengkap))}" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">description</i> Patroli (Word)
                                            </a>` : ''}
                                            ${data.outputPathPdfCipopMbg ? `<a href="hasil/${encodeURIComponent(getHasilFileName(data.outputPathPdfCipopMbg))}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">picture_as_pdf</i> Lampiran Amplifikasi Cipop (PDF)
                                            </a>` : ''}
                                            ${mbgExcelFile ? `<a href="hasil/${encodeURIComponent(mbgExcelFile)}" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">table_chart</i> Lampiran Link Amplifikasi (Excel)
                                            </a>` : ''}
                                        </div>
                                    </div>
                                `;
                            } else {
                                mbgLengkapResultContent.innerHTML = `
                                    <div class="alert alert-danger">
                                        <i class="material-icons-outlined">error</i>
                                        Pemrosesan Laporan MBG Lengkap gagal atau data tidak tersedia.
                                    </div>
                                `;
                            }
                        } else {
                            if (placeholder) placeholder.classList.remove('d-none');
                            mbgLengkapResultContent.classList.add('d-none');
                            mbgLengkapResultContent.innerHTML = '';
                        }
                    }

                    // Tampilkan hasil laporan Patroli Pagi
                    const pagiResultContent = document.getElementById('laporanPagiResult');
                    if (pagiResultContent) {
                        // Find the parent card and placeholder
                        const parentCard = pagiResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        if (selected.includes('Patroli Pagi')) {
                            debugLog('Patroli Pagi report type is selected, forcing display for debugging...');
                            
                            // Always show the section when Pagi is selected for debugging
                            if (placeholder) placeholder.classList.add('d-none');
                            pagiResultContent.classList.remove('d-none');
                            
                            if (data && (data.outputPathPagi || data.outputPathPdfPagi)) {
                                debugLog('Displaying Patroli Pagi results - files available');
                                
                                pagiResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli Pagi</label>
                                            <button type="button" class="btn btn-sm btn-outline-warning copy-btn" data-target="pagi-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="pagi-narrative" class="form-control result-textarea" rows="20">${data.narasiPatroliPagi ? data.narasiPatroliPagi : ''}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="d-flex flex-column gap-2">
                                            ${data.outputPathPagi ? `<a href="hasil/${encodeURIComponent(data.outputPathPagi.split('/').pop())}" class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">description</i> Patroli (Word)
                                            </a>` : '<div class="alert alert-warning small">File Word tidak tersedia</div>'}
                                            ${data.outputPathPdfPagi ? `<a href="hasil/${encodeURIComponent(data.outputPathPdfPagi.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">picture_as_pdf</i> Lampiran (PDF)
                                            </a>` : '<div class="alert alert-warning small">File PDF tidak tersedia</div>'}
                                        </div>
                                    </div>
                                `;
                            } else if (data && data.narasiPatroliPagi) {
                                debugLog('Displaying Patroli Pagi results - narasi only');
                                
                                pagiResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli Pagi</label>
                                            <button type="button" class="btn btn-sm btn-outline-warning copy-btn" data-target="pagi-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="pagi-narrative" class="form-control result-textarea" rows="20">${data.narasiPatroliPagi}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="alert alert-warning">
                                            <i class="material-icons-outlined">warning</i> 
                                            File laporan Patroli Pagi gagal dibuat, tapi narasi tersedia.
                                        </div>
                                    </div>
                                `;
                            } else {
                                debugLog('Displaying Patroli Pagi results - error state or no data');
                                
                                pagiResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli Pagi</label>
                                        </div>
                                        <textarea id="pagi-narrative" class="form-control result-textarea" rows="10">${data?.narasiPatroliPagi || 'Narasi tidak tersedia atau pemrosesan gagal'}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="alert alert-danger">
                                            <i class="material-icons-outlined">error</i> 
                                            Pemrosesan laporan Patroli Pagi gagal. 
                                            <details class="mt-2">
                                                <summary>Debug Info</summary>
                                                <pre class="mt-2 small">${JSON.stringify({
                                                    dataExists: Boolean(data),
                                                    hasOutputPathPagi: Boolean(data?.outputPathPagi),
                                                    hasOutputPathPdfPagi: Boolean(data?.outputPathPdfPagi),
                                                    hasNarasiPatroliPagi: Boolean(data?.narasiPatroliPagi),
                                                    outputPathPagiValue: data?.outputPathPagi || 'empty',
                                                    outputPathPdfPagiValue: data?.outputPathPdfPagi || 'empty'
                                                }, null, 2)}</pre>
                                            </details>
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            debugLog('No Patroli Pagi results to display - not selected');
                            // Reset to default state
                            if (placeholder) placeholder.classList.remove('d-none');
                            pagiResultContent.classList.add('d-none');
                            pagiResultContent.innerHTML = '';
                        }
                    }

                    // Tampilkan hasil laporan Patroli Umum
                    const bencanaResultContent = document.getElementById('laporanBencanaResult');
                    if (bencanaResultContent) {
                        // Find the parent card and placeholder
                        const parentCard = bencanaResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;
                        
                        if (selected.includes('Patroli Bencana')) {
                            debugLog('Patroli Bencana report type is selected, forcing display for debugging...');
                            
                            // Always show the section when Bencana is selected for debugging
                            if (placeholder) placeholder.classList.add('d-none');
                            bencanaResultContent.classList.remove('d-none');
                            
                            debugLog('Bencana data check:', {
                                hasOutputPathBencana: Boolean(data?.outputPathBencana),
                                hasOutputPathPdfBencana: Boolean(data?.outputPathPdfBencana),
                                narasiPatroliBencana: data?.narasiPatroliBencana || 'none',
                                hasNarasiPatroliBencana: Boolean(data?.narasiPatroliBencana),
                                dataExists: Boolean(data),
                                fullData: data
                            });
                            
                            if (data && (data.outputPathBencana || data.outputPathPdfBencana || data.narasiPatroliBencana)) {
                                debugLog('Displaying Patroli Bencana results - files or narasi available');
                                
                                bencanaResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli Umum</label>
                                            <button type="button" class="btn btn-sm btn-outline-danger copy-btn" data-target="bencana-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="bencana-narrative" class="form-control result-textarea" rows="20">${data.narasiPatroliBencana ? data.narasiPatroliBencana : ''}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="d-flex flex-column gap-2">
                                            ${data.outputPathBencana ? `<a href="hasil/${encodeURIComponent(data.outputPathBencana.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">description</i> Patroli (Word)
                                            </a>` : '<div class="alert alert-warning small">File Word tidak tersedia</div>'}
                                            ${data.outputPathPdfBencana ? `<a href="hasil/${encodeURIComponent(data.outputPathPdfBencana.split('/').pop())}" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">picture_as_pdf</i> Lampiran (PDF)
                                            </a>` : '<div class="alert alert-warning small">File PDF tidak tersedia</div>'}
                                        </div>
                                    </div>
                                `;
                            } else if (data && data.narasiPatroliBencana) {
                                debugLog('Displaying Patroli Bencana results - narasi only');
                                
                                bencanaResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli Umum</label>
                                            <button type="button" class="btn btn-sm btn-outline-danger copy-btn" data-target="bencana-narrative">
                                                <i class="material-icons-outlined" style="font-size: 16px;">content_copy</i> Copy
                                            </button>
                                        </div>
                                        <textarea id="bencana-narrative" class="form-control result-textarea" rows="20">${data.narasiPatroliBencana}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="alert alert-warning">
                                            <i class="material-icons-outlined">warning</i> 
                                            File laporan Patroli Umum gagal dibuat, tapi narasi tersedia.
                                        </div>
                                    </div>
                                `;
                            } else {
                                debugLog('Displaying Patroli Bencana results - error state or no data');
                                
                                bencanaResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-medium mb-0">Narasi Patroli Umum</label>
                                        </div>
                                        <textarea id="bencana-narrative" class="form-control result-textarea" rows="10">${data?.narasiPatroliBencana || 'Narasi tidak tersedia atau pemrosesan gagal'}</textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="alert alert-danger">
                                            <i class="material-icons-outlined">error</i> 
                                            Pemrosesan laporan Patroli Umum gagal.
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            debugLog('No Patroli Bencana results to display - not selected');
                            // Reset to default state
                            if (placeholder) placeholder.classList.remove('d-none');
                            bencanaResultContent.classList.add('d-none');
                            bencanaResultContent.innerHTML = '';
                        }
                    }

                    // Tampilkan hasil laporan PPT
                    const pptResultContent = document.getElementById('laporanPptResult');
                    if (pptResultContent) {
                        const parentCard = pptResultContent.closest('.card');
                        const placeholder = parentCard ? parentCard.querySelector('.result-placeholder') : null;

                        if (selected.includes('Laporan PPT')) {
                            if (placeholder) placeholder.classList.add('d-none');
                            pptResultContent.classList.remove('d-none');

                            if (data && data.outputPathPpt) {
                                pptResultContent.innerHTML = `
                                    <div class="mb-3">
                                        <label class="form-label fw-medium mb-0">Output Laporan PPT</label>
                                        <div class="alert alert-warning mt-2 mb-0">
                                            <i class="material-icons-outlined">event</i>
                                            Tanggal laporan: ${document.getElementById('tanggal')?.value || '-'}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-medium">Download Hasil</label>
                                        <div class="d-flex flex-column gap-2">
                                            <a href="hasil/${encodeURIComponent(data.outputPathPpt.split('/').pop())}" class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center gap-2" target="_blank">
                                                <i class="material-icons-outlined">slideshow</i> Laporan (PPT)
                                            </a>
                                        </div>
                                    </div>
                                `;
                            } else {
                                pptResultContent.innerHTML = `
                                    <div class="alert alert-danger">
                                        <i class="material-icons-outlined">error</i>
                                        File PPT belum berhasil dibuat.
                                    </div>
                                `;
                            }
                        } else {
                            if (placeholder) placeholder.classList.remove('d-none');
                            pptResultContent.classList.add('d-none');
                            pptResultContent.innerHTML = '';
                        }
                    }

                    // Scroll ke hasil laporan (prioritas KBD, lalu Landy, lalu Pagi, lalu Bencana)
                    setTimeout(function() {
                        let scrollTarget = null;
                        const kbdResultContent = document.getElementById('laporanKbdResultContent');
                        const landyResultContent = document.getElementById('laporanLandyResult');
                        const pagiResultContent = document.getElementById('laporanPagiResult');
                        const bencanaResultContent = document.getElementById('laporanBencanaResult');
                        const pptResultContent = document.getElementById('laporanPptResult');
                        
                        if (kbdResultContent && !kbdResultContent.classList.contains('d-none')) {
                            scrollTarget = kbdResultContent.closest('.card');
                        } else if (landyResultContent && !landyResultContent.classList.contains('d-none')) {
                            scrollTarget = landyResultContent.closest('.card');
                        } else if (pagiResultContent && !pagiResultContent.classList.contains('d-none')) {
                            scrollTarget = pagiResultContent.closest('.card');
                        } else if (bencanaResultContent && !bencanaResultContent.classList.contains('d-none')) {
                            scrollTarget = bencanaResultContent.closest('.card');
                        } else if (pptResultContent && !pptResultContent.classList.contains('d-none')) {
                            scrollTarget = pptResultContent.closest('.card');
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
    console.log('validateFormBasedOnReportTypes called');
    
    let alertDiv = document.getElementById('formAlert');
    if (alertDiv) alertDiv.remove();
    
    const selected = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked')).map(cb => cb.value);
    console.log('Selected report types:', selected);
    
    if (selected.length === 0) {
        console.log('No report types selected, validation failed');
        showFormAlert('Pilih minimal satu jenis laporan.', document.getElementById('step-1'));
        return false;
    }
    
    const tanggal = document.getElementById('tanggal').value;
    console.log('Date value:', tanggal);
    if (!tanggal) {
        console.log('No date selected, validation failed');
        showFormAlert('Tanggal laporan harus diisi.', document.getElementById('step-2'));
        return false;
    }
    
    console.log('Basic validation passed, checking specific reports...');
    
    // Validasi Patrol Report (hanya untuk jenis laporan non-khusus)
    const reportsNeedPatrol = ['Laporan KBD', 'Patroli Landy', 'Patroli Pagi', 'Patroli Bencana', 'Laporan MBG Lengkap'];
    const hasNonKhusus = selected.some(type => reportsNeedPatrol.includes(type));
    console.log('hasNonKhusus:', hasNonKhusus);
    
    if (hasNonKhusus) {
        console.log('Has non-khusus reports, validating patrol report...');
        const patrolReport = document.getElementById('patrolReport').value.trim();
        if (!patrolReport) {
            showFormAlert('Input Patrol Report tidak boleh kosong.', document.getElementById('step-3'));
            return false;
        }
        
        // Validasi format patrol report berdasarkan jenis laporan
        // Normalize line breaks for cross-browser compatibility (Firefox uses \r\n, Chrome uses \n)
        let normalizedPatrolReport = patrolReport.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        
        // Strip optional field labels for Patroli Landy or Patroli Bencana - support multiple variations
        const isPatroliLandy = selected.includes('Patroli Landy');
        const isPatroliBencana = selected.includes('Patroli Bencana');
        const isMbgLengkap = selected.includes('Laporan MBG Lengkap');
        const isLandyOrBencanaOrMbg = isPatroliLandy || isPatroliBencana || isMbgLengkap;
        
        if (isLandyOrBencanaOrMbg) {
            console.log('Patroli Landy/Bencana/MBG Lengkap detected - stripping labels (Landy:', isPatroliLandy, 'Bencana:', isPatroliBencana, 'MBG Lengkap:', isMbgLengkap + ')');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^nama[\s_-]*akun\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^link\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^kategori\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^narasi\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^profiling\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^tanggal[\s_-]*postingan\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^wilayah\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^korelasi\s*:\s*/gim, '');
            normalizedPatrolReport = normalizedPatrolReport.replace(/^afiliasi\s*:\s*/gim, '');
        } else {
            console.log('Non-Landy/Bencana patrol - no label stripping');
        }
        
        const patrolLines = normalizedPatrolReport.split('\n').map(l => l.trim()).filter(l => l !== '');
        
        if (isLandyOrBencanaOrMbg) {
            // CHECK IF MULTI-LINE PROFILING FORMAT - Skip validation if detected
            // Support both old format (profiling:\nNama:) and new format (profiling:\nNik: or profiling:\nKK:)
            // Simple pattern: profiling: followed by newline and any field:value pattern (more flexible)
            const hasMultiLineProfiling = /profiling\s*:\s*\n\s*[A-Za-z\s\/\-]+\s*:/i.test(patrolReport);
            
            if (hasMultiLineProfiling) {
                console.log('✅ Multi-line profiling format detected in ajax-handler - Skip 9-line validation');
                // Skip validation for multi-line format
            } else {
                // Untuk Patroli Landy/Bencana: harus kelipatan 9 baris (nama akun, link, kategori, narasi, profiling, tanggal_postingan, wilayah, korelasi, afiliasi)
                if (patrolLines.length % 9 !== 0) {
                    let reportTypeName = 'Patroli Landy';
                    if (isPatroliBencana) reportTypeName = 'Patroli Umum';
                    if (isMbgLengkap) reportTypeName = 'Laporan MBG Lengkap';
                    showFormAlert('Format Patrol Report untuk ' + reportTypeName + ' tidak valid. Harus terdiri dari kelompok 9 baris (nama akun, link, kategori, narasi, profiling, tanggal_postingan, wilayah, korelasi, afiliasi).<br><br><small><strong>TIP:</strong> Gunakan format multi-line profiling terstruktur (lihat contoh di Step 3).</small>', document.getElementById('step-3'));
                    return false;
                }
            }
        } else {
            // Untuk laporan lainnya: harus kelipatan 4 baris (nama akun, link, kategori, narasi)
            if (patrolLines.length % 4 !== 0) {
                showFormAlert('Format Patrol Report tidak valid. Harus terdiri dari kelompok 4 baris (nama akun, link, kategori, narasi).', document.getElementById('step-3'));
                return false;
            }
        }
    }
    
    // Validasi Patroli Pagi
    if (selected.includes('Patroli Pagi')) {
        const upaya = document.getElementById('inputUpaya').value.trim();
        if (!upaya) {
            showFormAlert('Input Upaya Patroli Pagi tidak boleh kosong.', document.getElementById('step3-inputUpaya'));
            return false;
        }
        
        // Validasi format upaya: harus kelipatan 3 baris
        // Normalize line breaks for cross-browser compatibility
        let normalizedUpaya = upaya.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        
        // Strip optional field labels to support both formats
        normalizedUpaya = normalizedUpaya.replace(/^(nama.{0,1}akun|link|narasi)\s*:\s*/gim, '');
        
        const upayaLines = normalizedUpaya.split('\n').map(l => l.trim()).filter(l => l !== '');
        if (upayaLines.length % 3 !== 0) {
            showFormAlert('Format Input Upaya tidak valid. Harus terdiri dari kelompok 3 baris (nama akun, link, narasi).', document.getElementById('step3-inputUpaya'));
            return false;
        }
    }
    
    // Validasi Laporan KBD
    if (selected.includes('Laporan KBD')) {
        console.log('🔍 Starting Laporan KBD validation...');
        
        const excelFiles = document.getElementById('excelFiles').files;
        console.log('  - Excel files count:', excelFiles ? excelFiles.length : 0);
        if (!excelFiles || excelFiles.length < 1) {
            console.log('  ❌ Excel files validation failed');
            showFormAlert('Upload minimal 1 file Excel Cipop.', document.getElementById('excelFiles'));
            return false;
        }
        
        // Validasi radio button cipopImageType
        const cipopTypeRadios = document.querySelectorAll('input[name="cipopImageType"]');
        const cipopTypeChecked = Array.from(cipopTypeRadios).some(radio => radio.checked);
        console.log('  - Cipop image type selected:', cipopTypeChecked);
        if (!cipopTypeChecked) {
            console.log('  ❌ Cipop image type not selected');
            showFormAlert('Pilih metode upload gambar cipop (Upload File atau Screenshot).', document.querySelector('input[name="cipopImageType"]').parentNode);
            return false;
        }
        
        const cipopType = document.querySelector('input[name="cipopImageType"]:checked').value;
        console.log('  - Cipop type:', cipopType);
        
        if (cipopType === 'upload') {
            const files = document.getElementById('imageFiles').files;
            console.log('  - Image files count:', files ? files.length : 0);
            if (!files || files.length < 1) {
                console.log('  ❌ Image files validation failed - empty');
                showFormAlert('Upload minimal 1 gambar cipop.', document.getElementById('imageFiles'));
                return false;
            }
            if (files.length > 8) {
                console.log('  ❌ Image files validation failed - too many');
                showFormAlert('Maksimal 8 gambar cipop yang dapat diupload.', document.getElementById('imageFiles'));
                return false;
            }
        } else if (cipopType === 'screenshot') {
            const cipopScreenshotValue = document.getElementById('cipopScreenshotLinks').value.trim();
            const normalizedCipopLinks = cipopScreenshotValue.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            const links = normalizedCipopLinks.split('\n').map(l => l.trim()).filter(l => l !== '');
            console.log('  - Screenshot links count:', links.length);
            if (links.length < 1) {
                console.log('  ❌ Screenshot links validation failed - empty');
                showFormAlert('Masukkan minimal 1 link untuk tangkapan layar cipop.', document.getElementById('cipopScreenshotLinks'));
                return false;
            }
            if (links.length > 8) {
                console.log('  ❌ Screenshot links validation failed - too many');
                showFormAlert('Maksimal 8 link tangkapan layar cipop yang dapat dimasukkan.', document.getElementById('cipopScreenshotLinks'));
                return false;
            }
            for (let i = 0; i < links.length; i++) {
                if (!/^https?:\/\/.+/i.test(links[i])) {
                    console.log(`  ❌ Link #${i+1} validation failed - invalid URL`);
                    showFormAlert(`Link cipop #${i+1} tidak valid. Semua link harus diawali http:// atau https://`, document.getElementById('cipopScreenshotLinks'));
                    return false;
                }
            }
        }
        console.log('  ✅ Laporan KBD validation passed');
    }

    // Validasi Laporan MBG Lengkap
    if (selected.includes('Laporan MBG Lengkap')) {
        const judulMbgValue = document.getElementById('judulMbgLengkap')?.value || '';
        const customJudulMbg = document.getElementById('judulMbgLengkapCustom')?.value.trim() || '';
        if (!judulMbgValue || (judulMbgValue === 'custom' && !customJudulMbg)) {
            showFormAlert('Judul Laporan MBG Lengkap wajib diisi.', document.getElementById('step3-judulMbgLengkap'));
            return false;
        }

        const excelFiles = document.getElementById('excelFiles')?.files;
        if (!excelFiles || excelFiles.length < 1) {
            showFormAlert('Upload minimal 1 file Excel Cipop untuk Laporan MBG Lengkap.', document.getElementById('excelFiles'));
            return false;
        }

        const cipopType = document.querySelector('input[name="cipopImageType"]:checked')?.value;
        if (cipopType === 'upload') {
            const files = document.getElementById('imageFiles')?.files;
            if (!files || files.length < 1) {
                showFormAlert('Upload minimal 1 gambar cipop untuk Laporan MBG Lengkap.', document.getElementById('imageFiles'));
                return false;
            }
        } else if (cipopType === 'screenshot') {
            const links = (document.getElementById('cipopScreenshotLinks')?.value || '').trim().split('\n').filter(l => l.trim());
            if (links.length < 1) {
                showFormAlert('Masukkan minimal 1 link tangkapan layar cipop.', document.getElementById('cipopScreenshotLinks'));
                return false;
            }
        }
    }

    // Validasi Laporan PPT
    if (selected.includes('Laporan PPT')) {
        const pptFiles = document.getElementById('pptImageFiles')?.files;
        if (!pptFiles || pptFiles.length < 1) {
            showFormAlert('Upload minimal 1 gambar untuk Laporan PPT.', document.getElementById('pptImageFiles'));
            return false;
        }
        if (pptFiles.length > 6) {
            showFormAlert('Maksimal 6 gambar untuk Laporan PPT.', document.getElementById('pptImageFiles'));
            return false;
        }
    }

    // Validasi Laporan Khusus
    if (selected.includes('Laporan Khusus')) {
        console.log('Starting Laporan Khusus validation...');
        
        // Validasi Patrol Report Khusus
        const patrolReportKhusus = document.getElementById('patrolReportKhusus').value.trim();
        console.log('patrolReportKhusus value:', patrolReportKhusus.length > 0 ? 'has content' : 'empty');
        
        if (!patrolReportKhusus) {
            console.log('patrolReportKhusus validation failed - empty');
            showFormAlert('Input Patrol Report Khusus tidak boleh kosong.', document.getElementById('patrolReportKhusus'));
            return false;
        }
        
        // Validasi format patrol report khusus: harus kelipatan 4 baris
        // Normalize line breaks for cross-browser compatibility
        const normalizedPatrolReportKhusus = patrolReportKhusus.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        const patrolLinesKhusus = normalizedPatrolReportKhusus.split('\n').map(l => l.trim()).filter(l => l !== '');
        if (patrolLinesKhusus.length % 4 !== 0) {
            showFormAlert('Format Patrol Report Khusus tidak valid. Harus terdiri dari kelompok 4 baris (nama akun, link, kategori, narasi).', document.getElementById('patrolReportKhusus'));
            return false;
        }
        
        const tema = document.getElementById('inputTema').value.trim();
        console.log('inputTema value:', tema.length > 0 ? 'has content' : 'empty');
        
        if (!tema) {
            console.log('inputTema validation failed - empty');
            showFormAlert('Input Tema Laporan Khusus tidak boleh kosong.', document.getElementById('inputTema'));
            return false;
        }
        
        const excelFilesKhusus = document.getElementById('excelFilesKhusus').files;
        console.log('excelFilesKhusus files count:', excelFilesKhusus ? excelFilesKhusus.length : 'element not found');
        
        if (!excelFilesKhusus || excelFilesKhusus.length < 1) {
            console.log('excelFilesKhusus validation failed - no files');
            showFormAlert('Upload minimal 1 file Excel Cipop untuk Laporan Khusus.', document.getElementById('excelFilesKhusus'));
            return false;
        }
        
        // Validasi radio button cipopImageTypeKhusus
        const cipopTypeKhususRadios = document.querySelectorAll('input[name="cipopImageTypeKhusus"]');
        const cipopTypeKhususChecked = Array.from(cipopTypeKhususRadios).some(radio => radio.checked);
        console.log('cipopImageTypeKhusus radios found:', cipopTypeKhususRadios.length);
        console.log('cipopImageTypeKhusus checked:', cipopTypeKhususChecked);
        
        if (!cipopTypeKhususChecked) {
            console.log('cipopImageTypeKhusus validation failed - none checked');
            showFormAlert('Pilih metode upload gambar cipop untuk Laporan Khusus (Upload File atau Screenshot).', document.querySelector('input[name="cipopImageTypeKhusus"]').parentNode);
            return false;
        }
        
        const cipopTypeKhusus = document.querySelector('input[name="cipopImageTypeKhusus"]:checked').value;
        if (cipopTypeKhusus === 'upload') {
            const filesKhusus = document.getElementById('imageFilesKhusus').files;
            if (!filesKhusus || filesKhusus.length < 1) {
                showFormAlert('Upload minimal 1 gambar cipop untuk Laporan Khusus.', document.getElementById('imageFilesKhusus'));
                return false;
            }
            if (filesKhusus.length > 8) {
                showFormAlert('Maksimal 8 gambar cipop yang dapat diupload untuk Laporan Khusus.', document.getElementById('imageFilesKhusus'));
                return false;
            }
        } else if (cipopTypeKhusus === 'screenshot') {
            const cipopScreenshotKhususValue = document.getElementById('cipopScreenshotLinksKhusus').value.trim();
            const normalizedCipopLinksKhusus = cipopScreenshotKhususValue.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            const linksKhusus = normalizedCipopLinksKhusus.split('\n').map(l => l.trim()).filter(l => l !== '');
            if (linksKhusus.length < 1) {
                showFormAlert('Masukkan minimal 1 link untuk tangkapan layar cipop Laporan Khusus.', document.getElementById('cipopScreenshotLinksKhusus'));
                return false;
            }
            if (linksKhusus.length > 8) {
                showFormAlert('Maksimal 8 link tangkapan layar cipop yang dapat dimasukkan untuk Laporan Khusus.', document.getElementById('cipopScreenshotLinksKhusus'));
                return false;
            }
            for (let i = 0; i < linksKhusus.length; i++) {
                if (!/^https?:\/\/.+/i.test(linksKhusus[i])) {
                    showFormAlert(`Link cipop Laporan Khusus #${i+1} tidak valid. Semua link harus diawali http:// atau https://`, document.getElementById('cipopScreenshotLinksKhusus'));
                    return false;
                }
            }
        }
        
        // Validasi Screenshot Patroli Khusus
        const patroliTypeKhususRadios = document.querySelectorAll('input[name="patroliScreenshotTypeKhusus"]');
        const patroliTypeKhususChecked = Array.from(patroliTypeKhususRadios).some(radio => radio.checked);
        console.log('patroliScreenshotTypeKhusus radios found:', patroliTypeKhususRadios.length);
        console.log('patroliScreenshotTypeKhusus checked:', patroliTypeKhususChecked);
        
        if (!patroliTypeKhususChecked) {
            console.log('patroliScreenshotTypeKhusus validation failed - none checked');
            showFormAlert('Pilih metode upload screenshot patroli khusus (Upload File atau Screenshot).', document.querySelector('input[name="patroliScreenshotTypeKhusus"]').parentNode);
            return false;
        }
        
        const patroliTypeKhusus = document.querySelector('input[name="patroliScreenshotTypeKhusus"]:checked').value;
        if (patroliTypeKhusus === 'upload') {
            const ssPatroliKhusus = document.getElementById('screenshotPatroliKhusus').files;
            if (!ssPatroliKhusus || ssPatroliKhusus.length < 1) {
                showFormAlert('Upload minimal 1 screenshot patroli khusus.', document.getElementById('screenshotPatroliKhusus'));
                return false;
            }
            
            // Pastikan jumlah screenshot cukup untuk jumlah patrol entries khusus
            if (patrolLinesKhusus.length / 4 > ssPatroliKhusus.length) {
                showFormAlert(`Upload screenshot patroli khusus kurang. Minimal harus sama dengan jumlah patrol report khusus (${Math.ceil(patrolLinesKhusus.length / 4)}).`, document.getElementById('screenshotPatroliKhusus'));
                return false;
            }
        }
        
        console.log('Laporan Khusus validation completed successfully!');
    }
    
    // Validasi radio button patroliScreenshotType (kecuali Laporan Khusus yang sudah terpisah)
    // Skip this validation if ONLY "Laporan Khusus" is selected
    const isOnlyLaporanKhusus = selected.length === 1 && selected.includes('Laporan Khusus');
    console.log('isOnlyLaporanKhusus:', isOnlyLaporanKhusus);
    
    // Early return for "Laporan Khusus" only case
    if (isOnlyLaporanKhusus) {
        console.log('All validation passed successfully! (Laporan Khusus only)');
        debugLog('Form validation completed successfully', { selectedReports: selected, timestamp: new Date().toLocaleTimeString() });
        return true;
    }
    
    // Only validate patrol screenshots if we have non-Khusus reports AND it's not only Laporan Khusus
    if (!isOnlyLaporanKhusus && hasNonKhusus) {
        console.log('Validating patroliScreenshotType for non-khusus reports...');
        
        const patroliTypeRadios = document.querySelectorAll('input[name="patroliScreenshotType"]');
        const patroliTypeChecked = Array.from(patroliTypeRadios).some(radio => radio.checked);
        if (!patroliTypeChecked) {
            console.log('patroliScreenshotType validation failed - none checked');
            showFormAlert('Pilih metode upload screenshot patroli (Upload File atau Screenshot).', document.querySelector('input[name="patroliScreenshotType"]').parentNode);
            return false;
        }
        
        // Validasi Screenshot Patroli - penting untuk jenis laporan selain Laporan Khusus
        // BUT only if the screenshot patrol section is actually visible
        const screenshotPatrolSection = document.getElementById('step4-screenshotPatrolUmum');
        const isScreenshotPatrolVisible = screenshotPatrolSection && !screenshotPatrolSection.classList.contains('d-none');
        
        if (isScreenshotPatrolVisible) {
            const patroliType = document.querySelector('input[name="patroliScreenshotType"]:checked').value;
            if (patroliType === 'upload') {
                const ssPatroli = document.getElementById('screenshotPatroli').files;
                if (!ssPatroli || ssPatroli.length < 1) {
                    showFormAlert('Upload minimal 1 screenshot patroli.', document.getElementById('screenshotPatroli'));
                    return false;
                }
                
                // Pastikan jumlah screenshot sama dengan jumlah patrol entries untuk Patroli Landy
                if (hasNonKhusus && selected.includes('Patroli Landy')) {
                    const patrolReport = document.getElementById('patrolReport').value.trim();
                    const normalizedPatrolReport2 = patrolReport.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                    
                    // CHECK IF MULTI-LINE PROFILING FORMAT
                    const hasMultiLineProfilingScreenshot = /profiling:\s*\n\s*Nama:/i.test(normalizedPatrolReport2);
                    
                    if (hasMultiLineProfilingScreenshot) {
                        // For multi-line format, count by "nama akun:" occurrences
                        const namaAkunMatches = normalizedPatrolReport2.match(/^nama[\s_-]*akun\s*:/gim);
                        const expectedCount = namaAkunMatches ? namaAkunMatches.length : 1;
                        
                        console.log('📸 Screenshot validation for multi-line format:');
                        console.log('  - Screenshot count:', ssPatroli.length);
                        console.log('  - Expected count (nama akun):', expectedCount);
                        
                        if (ssPatroli.length !== expectedCount) {
                            showFormAlert(`Jumlah screenshot patroli (${ssPatroli.length}) harus sama dengan jumlah data patroli (${expectedCount}). Harap upload tepat ${expectedCount} foto patroli.`, document.getElementById('screenshotPatroli'));
                            return false;
                        }
                    } else {
                        // Standard 9-line format validation
                        const patrolLines = normalizedPatrolReport2.split('\n').map(l => l.trim()).filter(l => l !== '');
                        const expectedCount = Math.ceil(patrolLines.length / 9); // 9 fields per entry for Patroli Landy
                        
                        console.log('📸 Screenshot validation for standard 9-line format:');
                        console.log('  - Screenshot count:', ssPatroli.length);
                        console.log('  - Expected count (lines/9):', expectedCount);
                        
                        if (ssPatroli.length !== expectedCount) {
                            showFormAlert(`Jumlah screenshot patroli (${ssPatroli.length}) harus sama dengan jumlah data patroli (${expectedCount}). Harap upload tepat ${expectedCount} foto patroli.`, document.getElementById('screenshotPatroli'));
                            return false;
                        }
                    }
                }
            } else if (patroliType === 'screenshot') {
                const patroliLinks = document.getElementById('patroliScreenshotLinks');
                if (patroliLinks && patroliLinks.value.trim() === '') {
                    showFormAlert('Masukkan minimal 1 link screenshot patroli.', document.getElementById('patroliScreenshotLinks'));
                    return false;
                }
                
                // Validasi format link patroli
                if (patroliLinks) {
                    const normalizedPatroliLinks = patroliLinks.value.trim().replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                    const links = normalizedPatroliLinks.split('\n').map(l => l.trim()).filter(l => l !== '');
                    
                    // Untuk Patroli Landy, pastikan jumlah link sama dengan jumlah patrol entries
                    if (hasNonKhusus && selected.includes('Patroli Landy')) {
                        const patrolReport = document.getElementById('patrolReport').value.trim();
                        const normalizedPatrolReport3 = patrolReport.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                        const patrolLines = normalizedPatrolReport3.split('\n').map(l => l.trim()).filter(l => l !== '');
                        const expectedCount = Math.ceil(patrolLines.length / 9); // 9 fields per entry for Patroli Landy
                        if (links.length !== expectedCount) {
                            showFormAlert(`Jumlah link screenshot patroli (${links.length}) harus sama dengan jumlah data patroli (${expectedCount}). Harap masukkan tepat ${expectedCount} link.`, document.getElementById('patroliScreenshotLinks'));
                            return false;
                        }
                    }
                    
                    for (let i = 0; i < links.length; i++) {
                        if (!/^https?:\/\/.+/i.test(links[i])) {
                            showFormAlert(`Link screenshot patroli #${i+1} tidak valid. Semua link harus diawali http:// atau https://`, document.getElementById('patroliScreenshotLinks'));
                            return false;
                        }
                    }
                }
            }
        } else {
            console.log('Screenshot patrol section is hidden, skipping patrol screenshot validation');
        }
    }
    
    // Validasi khusus untuk Patroli Landy / Laporan MBG Lengkap (RAS) - skip jika hanya Laporan Khusus
    if (!isOnlyLaporanKhusus && (selected.includes('Patroli Landy') || selected.includes('Laporan MBG Lengkap'))) {
        const landyOrMbgLabel = selected.includes('Laporan MBG Lengkap') ? 'Laporan MBG Lengkap' : 'Patroli Landy';
        console.log('Validating ' + landyOrMbgLabel + ' (RAS)...');
        // Validasi input patrol report sudah dilakukan di atas
        if (hasNonKhusus) {
            const patrolReport = document.getElementById('patrolReport').value.trim();
            if (!patrolReport) {
                showFormAlert('Input Patrol Report tidak boleh kosong.', document.getElementById('step-3'));
                return false;
            }
        }
        
        // Validasi upload file RAS (Required for Landy)
        const rasFiles = document.getElementById('rasFiles');
        if (rasFiles) {
            debugLog('Validating RAS files for Landy');
            const rasFileList = rasFiles.files;
            if (!rasFileList || rasFileList.length < 1) {
                showFormAlert('Upload minimal 1 gambar RAS untuk ' + landyOrMbgLabel + '.', document.getElementById('rasFiles'));
                return false;
            }
            
            // Validasi jumlah RAS harus sama dengan jumlah data patroli
            if (hasNonKhusus) {
                const patrolReport = document.getElementById('patrolReport').value.trim();
                const normalizedPatrolReport4 = patrolReport.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                
                // CHECK IF MULTI-LINE PROFILING FORMAT
                const hasMultiLineProfilingRAS = /profiling:\s*\n\s*Nama:/i.test(normalizedPatrolReport4);
                
                let expectedCount;
                if (hasMultiLineProfilingRAS) {
                    // Count by "nama akun:" occurrences
                    const namaAkunMatches = normalizedPatrolReport4.match(/^nama[\s_-]*akun\s*:/gim);
                    expectedCount = namaAkunMatches ? namaAkunMatches.length : 1;
                    console.log('📸 RAS validation - Multi-line format: Expected', expectedCount);
                } else {
                    // Standard 9-line format
                    const patrolLines = normalizedPatrolReport4.split('\n').map(l => l.trim()).filter(l => l !== '');
                    expectedCount = Math.ceil(patrolLines.length / 9);
                    console.log('📸 RAS validation - Standard format: Expected', expectedCount);
                }
                
                if (rasFileList.length !== expectedCount) {
                    showFormAlert(`Jumlah foto RAS/Upaya (${rasFileList.length}) harus sama dengan jumlah data patroli (${expectedCount}). Harap upload tepat ${expectedCount} foto RAS.`, document.getElementById('rasFiles'));
                    return false;
                }
            }
            
            // Log the RAS files count for debugging
            debugLog('RAS files count:', rasFileList.length);
        } else {
            debugLog('RAS files element not found');
        }
        
        // Validasi upload file Profiling (Required for Landy)
        const profilingFiles = document.getElementById('profilingFiles');
        if (profilingFiles) {
            debugLog('Validating Profiling files for Landy');
            const profilingFileList = profilingFiles.files;
            if (!profilingFileList || profilingFileList.length < 1) {
                showFormAlert('Upload minimal 1 foto profiling untuk ' + landyOrMbgLabel + '.', document.getElementById('profilingFiles'));
                return false;
            }
            
            // Validasi jumlah Profiling harus sama dengan jumlah data patroli
            if (hasNonKhusus) {
                const patrolReport = document.getElementById('patrolReport').value.trim();
                const normalizedPatrolReport5 = patrolReport.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                
                // CHECK IF MULTI-LINE PROFILING FORMAT
                const hasMultiLineProfilingProfiling = /profiling:\s*\n\s*Nama:/i.test(normalizedPatrolReport5);
                
                let expectedCount;
                if (hasMultiLineProfilingProfiling) {
                    // Count by "nama akun:" occurrences
                    const namaAkunMatches = normalizedPatrolReport5.match(/^nama[\s_-]*akun\s*:/gim);
                    expectedCount = namaAkunMatches ? namaAkunMatches.length : 1;
                    console.log('📸 Profiling validation - Multi-line format: Expected', expectedCount);
                } else {
                    // Standard 9-line format
                    const patrolLines = normalizedPatrolReport5.split('\n').map(l => l.trim()).filter(l => l !== '');
                    expectedCount = Math.ceil(patrolLines.length / 9);
                    console.log('📸 Profiling validation - Standard format: Expected', expectedCount);
                }
                
                if (profilingFileList.length !== expectedCount) {
                    showFormAlert(`Jumlah foto profiling (${profilingFileList.length}) harus sama dengan jumlah data patroli (${expectedCount}). Harap upload tepat ${expectedCount} foto profiling.`, document.getElementById('profilingFiles'));
                    return false;
                }
            }
            
            // Log the Profiling files count for debugging
            debugLog('Profiling files count:', profilingFileList.length);
        } else {
            debugLog('Profiling files element not found');
        }
    }
    
    // Validasi Patroli Landy atau Patroli Pagi untuk upaya screenshot - skip jika hanya Laporan Khusus
    if (!isOnlyLaporanKhusus && (selected.includes('Patroli Landy') || selected.includes('Patroli Pagi'))) {
        console.log('Validating upaya screenshot for Patroli Landy/Pagi...');
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
                const normalizedUpayaLinks = upayaLinks.value.trim().replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                const links = normalizedUpayaLinks.split('\n').map(l => l.trim()).filter(l => l !== '');
                for (let i = 0; i < links.length; i++) {
                    if (!/^https?:\/\/.+/i.test(links[i])) {
                        showFormAlert(`Link screenshot upaya #${i+1} tidak valid. Semua link harus diawali http:// atau https://`, document.getElementById('upayaScreenshotLinks'));
                        return false;
                    }
                }
            }
        }
    }
    
    console.log('All validation passed successfully!');
    debugLog('Form validation completed successfully', { selectedReports: selected, timestamp: new Date().toLocaleTimeString() });
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

/**
 * Preview uploaded files
 * @param {HTMLInputElement} input - The file input element
 * @param {string} previewContainerId - ID of the preview container
 */
function previewFiles(input, previewContainerId) {
    const previewContainer = document.getElementById(previewContainerId);
    if (!previewContainer) return;
    
    // Clear previous previews
    previewContainer.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        const filesArray = Array.from(input.files);
        
        filesArray.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'file-preview-item d-inline-block me-2 mb-2';
                    previewItem.innerHTML = `
                        <div class="position-relative">
                            <img src="${e.target.result}" class="preview-image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                            <div class="preview-filename text-center mt-1" style="font-size: 0.75rem; max-width: 80px; word-wrap: break-word;">${file.name}</div>
                        </div>
                    `;
                    previewContainer.appendChild(previewItem);
                };
            }
        });
    }
}
