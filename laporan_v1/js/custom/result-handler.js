/**
 * Result Display Handler
 * Manages the display of processing results in the result cards
 */

// Format to display results in the cards
function displayResults(data) {
    // Process each result type
    if (data.laporanKbd && data.laporanKbd.length > 0) {
        displayResultInCard('laporanKbdResult', data.laporanKbd, 'primary');
    }
    
    if (data.laporanLandy && data.laporanLandy.length > 0) {
        displayResultInCard('laporanLandyResult', data.laporanLandy, 'success');
    }
    
    if (data.laporanPagi && data.laporanPagi.length > 0) {
        displayResultInCard('laporanPagiResult', data.laporanPagi, 'warning');
    }
}

// Display results in a specific card
function displayResultInCard(cardId, files, btnColorClass) {
    const card = document.getElementById(cardId);
    if (!card) return;
    
    // Find the result container inside the card
    const placeholder = card.querySelector('.result-placeholder');
    const resultContent = card.querySelector('.result-content');
    
    if (placeholder) placeholder.classList.add('d-none');
    if (resultContent) {
        resultContent.classList.remove('d-none');
        
        // Clear previous content
        resultContent.innerHTML = '';
        
        // Create content for each file
        files.forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'result-file-item';
            
            // File name with icon
            const fileName = document.createElement('div');
            fileName.className = 'result-file-name';
            
            // Choose icon based on file type
            let fileIcon = 'description';
            if (file.type === 'pdf') fileIcon = 'picture_as_pdf';
            else if (file.type === 'docx') fileIcon = 'article';
            else if (file.type === 'xlsx') fileIcon = 'table_chart';
            
            fileName.innerHTML = `<i class="material-icons-outlined small me-1">${fileIcon}</i> ${file.name}`;
            fileItem.appendChild(fileName);
            
            // Create button group
            const buttonGroup = document.createElement('div');
            buttonGroup.className = 'result-button-group mt-2';
            
            // View button if applicable (PDF files)
            if (file.type === 'pdf' && file.url) {
                const viewBtn = document.createElement('a');
                viewBtn.href = file.url;
                viewBtn.target = '_blank';
                viewBtn.className = `btn btn-sm btn-outline-${btnColorClass} result-button`;
                viewBtn.innerHTML = '<i class="material-icons-outlined" style="font-size:14px;">visibility</i> View';
                buttonGroup.appendChild(viewBtn);
            }
            
            // Download button
            if (file.url) {
                const downloadBtn = document.createElement('a');
                downloadBtn.href = file.url;
                downloadBtn.download = file.name;
                downloadBtn.className = `btn btn-sm btn-${btnColorClass} result-button`;
                downloadBtn.innerHTML = '<i class="material-icons-outlined" style="font-size:14px;">download</i> Download';
                buttonGroup.appendChild(downloadBtn);
            }
            
            fileItem.appendChild(buttonGroup);
            resultContent.appendChild(fileItem);
        });
    }
}

// Add this function to handle detailed report displays with textareas
function displayDetailedReport(cardId, content, fileName, type) {
    const card = document.getElementById(cardId);
    if (!card) return;
    
    // Find the result container inside the card
    const placeholder = card.querySelector('.result-placeholder');
    const resultContent = card.querySelector('.result-content');
    
    if (placeholder) placeholder.classList.add('d-none');
    if (resultContent) {
        resultContent.classList.remove('d-none');
        
        // Create a textarea for the content
        resultContent.innerHTML = `
            <div class="mb-3">
                <textarea class="form-control result-textarea" readonly>${content}</textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-secondary small">${fileName}</span>
                <button class="btn btn-sm btn-primary result-button" onclick="copyToClipboard(this, '${cardId}')">
                    <i class="material-icons-outlined" style="font-size:14px;">content_copy</i> Copy
                </button>
            </div>
        `;
    }
}

// Helper function to copy textarea content to clipboard
function copyToClipboard(button, cardId) {
    const card = document.getElementById(cardId);
    if (!card) return;
    
    const textarea = card.querySelector('textarea');
    if (textarea) {
        textarea.select();
        document.execCommand('copy');
        
        // Change button text temporarily
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="material-icons-outlined" style="font-size:14px;">check</i> Copied!';
        
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    }
}
