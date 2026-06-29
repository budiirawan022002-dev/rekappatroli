/**
 * progress.js
 * Berisi fungsi-fungsi untuk mengelola progress bar dan debugging
 */

// Debug logger function with improved handling for screenshot messages
function debugLog(message, data = null) {
    const timestamp = new Date().toLocaleTimeString();
    const logMsg = `[${timestamp}] ${message}`;
    console.log(logMsg, data || '');
    
    // Add to debug info div if it exists
    const debugInfo = document.getElementById('debugInfo');
    if (debugInfo) {
        const msgElement = document.createElement('div');
        
        // Format specially for screenshot messages
        if (message.includes('Screenshot progress') || 
            (typeof data === 'object' && data && data.progress && 
             (data.progress.includes('tangkapan layar') || data.progress.includes('screenshot')))) {
            msgElement.innerHTML = `<span style="color:#28a745;font-weight:bold;">${logMsg}</span>`;
            if (data) {
                const progressText = data.progress || JSON.stringify(data).substring(0, 200);
                msgElement.innerHTML += `<br><span style="color:#17a2b8;margin-left:10px;">→ ${progressText}</span>`;
            }
            
            // Auto-scroll debug info to keep latest messages visible
            setTimeout(() => {
                debugInfo.scrollTop = 0;
            }, 50);
        } else {
            msgElement.innerText = logMsg + (data ? ': ' + JSON.stringify(data).substring(0, 200) : '');
        }
        
        debugInfo.prepend(msgElement);
        
        // Limit the number of debug messages to keep performance good
        if (debugInfo.children.length > 100) {
            debugInfo.removeChild(debugInfo.lastChild);
        }
    }
}

// Fungsi untuk menampilkan progress bar dengan tampilan yang lebih detail
function showProgressBar(status) {
    console.log('showProgressBar called with status:', status);
    debugLog('Progress bar shown', { status });
    
    // Wait for DOM to be ready
    if (document.readyState !== 'complete') {
        console.log('DOM not ready, waiting...');
        setTimeout(() => showProgressBar(status), 100);
        return;
    }
    
    const overlay = document.getElementById('progressOverlay');
    const bar = document.getElementById('progressBar');
    const statusDiv = document.getElementById('progressBarStatus');
    const serverMsg = document.getElementById('progressBarServerMsg');
    
    console.log('Progress elements found:', {
        overlay: Boolean(overlay),
        bar: Boolean(bar), 
        statusDiv: Boolean(statusDiv),
        serverMsg: Boolean(serverMsg)
    });
    
    if (!overlay) {
        console.error('CRITICAL: progressOverlay element not found!');
        console.log('Available elements with "progress" in ID:', 
            Array.from(document.querySelectorAll('[id*="progress"]')).map(el => el.id));
        return;
    }
    
    if (!bar) {
        console.error('CRITICAL: progressBar element not found!');
        return;
    }
    
    if (!statusDiv) {
        console.error('CRITICAL: progressBarStatus element not found!');
        return;
    }
    
    console.log('All required progress elements found, showing progress bar...');
    
    try {
        // Reset step indicators
        if (typeof resetProcessSteps === 'function') {
            resetProcessSteps();
        }
        
        // Display the overlay
        overlay.style.display = 'flex';
        console.log('Progress overlay display set to flex');
        
        bar.style.width = '10%';
        bar.innerText = '10%';
        statusDiv.innerText = status || 'Memulai proses...';
        
        console.log('Progress bar updated:', {
            width: bar.style.width,
            text: bar.innerText,
            status: statusDiv.innerText
        });
        
        // Clear previous server messages
        if (serverMsg) {
            serverMsg.innerHTML = '<p class="mb-1"><i class="material-icons-outlined" style="font-size:16px;vertical-align:-3px;">info</i> Memulai pemrosesan laporan...</p>';
        }
        
        // Map initial status to step indicator
        if (typeof mapProgressToStep === 'function') {
            mapProgressToStep(10, status);
        }
        
        // Show debug info automatically for screenshot-related processes
        if (status && (status.includes('tangkapan layar') || status.includes('screenshot'))) {
            const debugInfo = document.getElementById('debugInfo');
            const debugToggle = document.getElementById('debugInfoToggle');
            if (debugInfo && debugInfo.style.display === 'none' && debugToggle) {
                debugToggle.click();
            }
        }
        
        console.log('Progress bar setup completed successfully');
    } catch (error) {
        console.error('Error setting up progress bar:', error);
    }
}

// Fungsi untuk mengupdate progress bar dengan lebih informatif
function updateProgressBar(percent, status) {
    console.log('updateProgressBar called with:', { percent, status });
    
    // Generate a timestamp for the progress update
    const timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
    
    // Log progress with more detail based on type
    if (status && (status.includes('tangkapan layar') || status.includes('screenshot'))) {
        debugLog('Screenshot progress update', { timestamp, percent, status });
    } else {
        debugLog('Progress update', { timestamp, percent, status });
    }
    
    // Update the main progress bar
    const bar = document.getElementById('progressBar');
    const statusDiv = document.getElementById('progressBarStatus');
    
    console.log('updateProgressBar elements found:', {
        bar: Boolean(bar),
        statusDiv: Boolean(statusDiv)
    });
    
    if (bar) {
        bar.style.width = percent + '%';
        bar.innerText = percent + '%';
        console.log('Progress bar updated to:', percent + '%');
    } else {
        console.error('Progress bar element not found!');
    }
    
    if (statusDiv && status) {
        statusDiv.innerText = status;
        console.log('Status updated to:', status);
    } else {
        console.error('Status div not found or status empty:', { statusDiv: Boolean(statusDiv), status });
    }
    
    // Update step indicators if available
    if (typeof mapProgressToStep === 'function') {
        mapProgressToStep(percent, status);
    }
}

// Fungsi untuk mengupdate pesan dari server dengan format yang lebih baik
function updateServerProgress(msg, percent) {
    const timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
    
    // Add icon based on message type
    let iconName = 'info';
    let messageClass = '';
    
    if (msg) {
        if (msg.includes('tangkapan layar') || msg.includes('screenshot')) {
            iconName = 'photo_camera';
            messageClass = 'text-info fw-medium';
            debugLog('Screenshot server progress', { timestamp, msg, percent });
        } else if (msg.includes('Word') || msg.includes('PDF') || msg.includes('file')) {
            iconName = 'description';
            messageClass = 'text-primary';
            debugLog('Document server progress', { timestamp, msg, percent });
        } else if (msg.includes('selesai') || msg.includes('Selesai') || percent >= 95) {
            iconName = 'check_circle';
            messageClass = 'text-success fw-medium';
            debugLog('Completion server progress', { timestamp, msg, percent });
        } else {
            debugLog('Server progress update', { timestamp, msg, percent });
        }
    }
    
    // Update the server message area with formatted message
    const serverMsg = document.getElementById('progressBarServerMsg');
    if (serverMsg && msg) {
        // Append new message instead of replacing (limited to 5 messages)
        const newMessage = `<p class="mb-1 ${messageClass}"><i class="material-icons-outlined" style="font-size:16px;vertical-align:-3px;">${iconName}</i> <span class="text-muted small me-1">[${timestamp}]</span> ${msg}</p>`;
        
        // Keep only the last 5 messages
        const messages = serverMsg.querySelectorAll('p');
        if (messages.length >= 5) {
            serverMsg.removeChild(messages[0]); // Remove oldest message
        }
        
        // Add new message
        serverMsg.innerHTML += newMessage;
        
        // Auto-scroll to bottom
        serverMsg.scrollTop = serverMsg.scrollHeight;
    }
    
    // Update progress bar percentage if provided
    if (typeof percent === 'number') {
        updateProgressBar(percent, msg);
    }
}

// Fungsi untuk menyembunyikan progress bar
function hideProgressBar() {
    debugLog('Progress bar hidden');
    const overlay = document.getElementById('progressOverlay');
    if (overlay) overlay.style.display = 'none';
    updateProgressBar(0, '');
    updateServerProgress('');
}

// Fungsi untuk mengatur status tombol upload
function setUploadButtonState(disabled) {
    debugLog('Upload button state changed', { disabled });
    const btn = document.getElementById('btnUploadProses');
    const spinner = document.getElementById('btnLoadingSpinner');
    if (btn) btn.disabled = !!disabled;
    if (spinner) spinner.style.display = disabled ? 'inline-block' : 'none';
}

// Toggle debug info visibility
document.addEventListener('DOMContentLoaded', function() {
    console.log('Progress.js DOM ready - setting up debug toggle');
    
    const debugInfoToggle = document.getElementById('debugInfoToggle');
    const debugInfo = document.getElementById('debugInfo');
    
    if (debugInfoToggle && debugInfo) {
        debugInfoToggle.addEventListener('click', function() {
            if (debugInfo.style.display === 'none') {
                debugInfo.style.display = 'block';
                debugInfoToggle.textContent = 'Hide Debug Info';
            } else {
                debugInfo.style.display = 'none';
                debugInfoToggle.textContent = 'Show Debug Info';
            }
        });
    }
    
    // Test functions for validation only (console only)
    window.testValidationOnly = function() {
        console.log('=== TESTING VALIDATION ONLY ===');
        try {
            const result = validateFormBasedOnReportTypes();
            console.log('Validation result:', result);
            return result;
        } catch (error) {
            console.error('Validation test error:', error);
            return false;
        }
    };
    
    // Test function for Laporan Khusus progress (console only)
    window.testLaporanKhususProgress = function() {
        console.log('=== TESTING LAPORAN KHUSUS PROGRESS ===');
        showProgressBar('Testing Laporan Khusus...');
        updateProgressBar(10, 'Testing Laporan Khusus...');
        
        let progress = 10;
        const interval = setInterval(() => {
            progress += 10;
            if (progress <= 100) {
                updateServerProgress(`Test progres laporan khusus: ${progress}%`, progress);
                updateProgressBar(progress, `Test progres laporan khusus: ${progress}%`);
            } else {
                clearInterval(interval);
                setTimeout(() => {
                    hideProgressBar();
                    console.log('Test completed');
                }, 2000);
            }
        }, 500);
    };
});
