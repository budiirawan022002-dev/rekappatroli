/**
 * toast-notifications.js
 * Berisi fungsi-fungsi untuk menampilkan toast notifications
 */

// Map of toast types to Bootstrap classes and icons
const TOAST_TYPES = {
    success: {
        bgClass: 'bg-success',
        icon: 'check_circle'
    },
    error: {
        bgClass: 'bg-danger',
        icon: 'error'
    },
    warning: {
        bgClass: 'bg-warning',
        icon: 'warning'
    },
    info: {
        bgClass: 'bg-info',
        icon: 'info'
    }
};

/**
 * Display a toast notification
 * @param {string} type - Type of toast: 'success', 'error', 'warning', 'info'
 * @param {string} title - Toast title
 * @param {string} message - Toast message
 * @param {number} timeout - Time in ms before auto-hide (default: 5000)
 */
function showToast(type = 'info', title = '', message = '', timeout = 5000) {
    // Get toast container, create if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '5000';
        document.body.appendChild(toastContainer);
    }
    
    // Get toast settings
    const toastSettings = TOAST_TYPES[type] || TOAST_TYPES.info;
    
    // Create toast ID
    const toastId = 'toast-' + Date.now();
    
    // Create toast HTML
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center ${toastSettings.bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center">
                        <i class="material-icons-outlined me-2">${toastSettings.icon}</i>
                        <div>
                            ${title ? `<strong>${title}</strong>` : ''}
                            ${title && message ? '<br>' : ''}
                            ${message}
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Get toast element
    const toastElement = document.getElementById(toastId);
    
    // Initialize toast with bootstrap 
    const toast = new bootstrap.Toast(toastElement, {
        autohide: !!timeout,
        delay: timeout
    });
    
    // Show toast
    toast.show();
    
    // Remove toast from DOM after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
    
    // Return toast element for further manipulation
    return toastElement;
}
