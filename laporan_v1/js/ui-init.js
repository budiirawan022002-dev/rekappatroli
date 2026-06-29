/**
 * ui-init.js
 * Berisi fungsi-fungsi inisialisasi UI dan konfigurasi awal
 */

document.addEventListener('DOMContentLoaded', function() {
    // Toggle debug info visibility
    const debugToggle = document.getElementById('debugInfoToggle');
    const debugInfo = document.getElementById('debugInfo');
    if (debugToggle && debugInfo) {
        debugToggle.addEventListener('click', function() {
            if (debugInfo.style.display === 'none') {
                debugInfo.style.display = 'block';
                debugToggle.innerText = 'Hide Debug Info';
            } else {
                debugInfo.style.display = 'none';
                debugToggle.innerText = 'Show Debug Info';
            }
        });
    }

    // RAS Screenshot toggle for Landy/Bencana report
    const landyRasScreenshotSection = document.getElementById('landyRasScreenshotSection');
    const landyProfilingScreenshotSection = document.getElementById('landyProfilingScreenshotSection');
    const patroliLandyCheckbox = document.getElementById('patroliLandy');
    const patroliBencanaCheckbox = document.getElementById('patroliBencana');
    const upayaPatroliSection = document.getElementById('upayaPatroliSection');
    const patroliPagiCheckbox = document.getElementById('patroliPagi');
    
    if (patroliLandyCheckbox && patroliPagiCheckbox && landyRasScreenshotSection && upayaPatroliSection) {
        // Update handler for Landy checkbox
        patroliLandyCheckbox.addEventListener('change', function() {
            updateSectionVisibility();
        });
        
        // Update handler for Bencana checkbox
        if (patroliBencanaCheckbox) {
            patroliBencanaCheckbox.addEventListener('change', function() {
                updateSectionVisibility();
            });
        }
        
        // Update handler for Pagi checkbox
        patroliPagiCheckbox.addEventListener('change', function() {
            updateSectionVisibility();
        });
        
        // Function to update section visibility
        function updateSectionVisibility() {
            // Show RAS section if Landy or Bencana is selected
            const showRas = patroliLandyCheckbox.checked || (patroliBencanaCheckbox && patroliBencanaCheckbox.checked);
            landyRasScreenshotSection.style.display = showRas ? 'block' : 'none';
            
            // Show Profiling section if Landy or Bencana is selected
            if (landyProfilingScreenshotSection) {
                const showProfiling = patroliLandyCheckbox.checked || (patroliBencanaCheckbox && patroliBencanaCheckbox.checked);
                landyProfilingScreenshotSection.style.display = showProfiling ? 'block' : 'none';
            }
            
            // Show Upaya section only if Pagi is selected
            upayaPatroliSection.style.display = patroliPagiCheckbox.checked ? 'block' : 'none';
            
            debugLog('Section visibility updated', {
                landySelected: patroliLandyCheckbox.checked,
                bencanaSelected: patroliBencanaCheckbox ? patroliBencanaCheckbox.checked : false,
                pagiSelected: patroliPagiCheckbox.checked,
                showRas: landyRasScreenshotSection.style.display,
                showProfiling: landyProfilingScreenshotSection ? landyProfilingScreenshotSection.style.display : 'N/A',
                showUpaya: upayaPatroliSection.style.display
            });
        }
    }

    // Control visibility of Patroli sections based on selected report types
    const step3InputUpaya = document.getElementById('step3-inputUpaya');
    
    // Setup initial handlers for checkbox changes
    if (patroliLandyCheckbox && patroliPagiCheckbox) {
        // Handle Landy checkbox
        patroliLandyCheckbox.addEventListener('change', updateFormVisibility);
        
        // Handle Bencana checkbox
        if (patroliBencanaCheckbox) {
            patroliBencanaCheckbox.addEventListener('change', updateFormVisibility);
        }
        
        // Handle Pagi checkbox
        patroliPagiCheckbox.addEventListener('change', updateFormVisibility);
        
        // Initialize visibility
        updateFormVisibility();
    }
    
    // Function to update form visibility based on selections
    function updateFormVisibility() {
        // For Step 3: Only show upaya input if Patroli Pagi is selected
        if (step3InputUpaya) {
            step3InputUpaya.classList.toggle('d-none', !patroliPagiCheckbox.checked);
        }
        
        // These will apply when we get to step 4
        if (upayaPatroliSection && landyRasScreenshotSection) {
            // For Step 4: Only show upaya for Patroli Pagi
            const landyChecked = patroliLandyCheckbox.checked;
            const pagiChecked = patroliPagiCheckbox.checked;
            
            // Debugging log
            debugLog('Form visibility update', {
                pagiChecked: pagiChecked
            });
        }
    }
});
