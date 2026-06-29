/**
 * Functions for handling step indicators and form progress
 */

// Initialize indicators when page loads
document.addEventListener('DOMContentLoaded', function() {
  // Initial state for wizard form steps
  updateStepIndicators(1);
  
  // Initialize processing steps
  resetProcessSteps();
});

/**
 * Updates form wizard progress indicators based on current step
 * @param {number} currentStep - The current active step (1-4)
 */
function updateStepIndicators(step) {
  // Update progress bar
  const progressBar = document.getElementById('wizardProgressBar');
  if (progressBar) {
    progressBar.style.width = (step * 25) + '%';
  }
  
  // Reset all indicators
  for (let i = 1; i <= 4; i++) {
    const indicator = document.getElementById(`step${i}Indicator`);
    if (indicator) {
      if (i === step) {
        // Current step
        indicator.classList.remove('bg-light', 'text-dark', 'bg-primary');
        indicator.classList.add('bg-success');
      } else if (i < step) {
        // Completed step
        indicator.classList.remove('bg-light', 'text-dark', 'bg-primary');
        indicator.classList.add('bg-success');
        // Add checkmark for completed steps
        indicator.innerHTML = '<i class="bi bi-check"></i>';
      } else {
        // Future step
        indicator.classList.remove('bg-primary', 'bg-success');
        indicator.classList.add('bg-light', 'text-dark');
        indicator.innerText = i;
      }
    }
  }
}

// Step indicators will be called directly from custom.js functions

/**
 * Reset processing step indicators to initial state
 */
function resetProcessSteps() {
  const steps = document.querySelectorAll('.process-step');
  if (!steps.length) return;
  
  steps.forEach(step => {
    step.classList.remove('active', 'completed');
    const indicator = step.querySelector('.step-indicator');
    if (indicator) {
      indicator.style.background = '#ddd';
      indicator.style.color = '#555';
    }
  });
  
  const stepProgressBar = document.getElementById('stepProgressBar');
  if (stepProgressBar) {
    stepProgressBar.style.width = '0%';
  }
}

/**
 * Update process step indicator based on progress
 * @param {Number} stepNumber - Step number to activate (1-based)
 * @param {Boolean} completed - Whether the step is completed
 */
function updateProcessStep(stepNumber, completed = false) {
  const step = document.querySelector(`.process-step[data-step="${stepNumber}"]`);
  if (!step) return;
  
  const steps = document.querySelectorAll('.process-step');
  const totalSteps = steps.length;
  
  // Update previous steps as completed
  for (let i = 1; i < stepNumber; i++) {
    const prevStep = document.querySelector(`.process-step[data-step="${i}"]`);
    if (prevStep) {
      prevStep.classList.add('completed');
      prevStep.classList.remove('active');
      const indicator = prevStep.querySelector('.step-indicator');
      if (indicator) {
        indicator.style.background = '#28a745'; // Success green
        indicator.style.color = '#fff';
      }
    }
  }
  
  // Update current step
  step.classList.add(completed ? 'completed' : 'active');
  if (completed) step.classList.remove('active');
  
  const indicator = step.querySelector('.step-indicator');
  if (indicator) {
    indicator.style.background = completed ? '#28a745' : '#007bff'; // Blue for active, green for completed
    indicator.style.color = '#fff';
  }
  
  // Calculate and update progress percentage
  const completedSteps = document.querySelectorAll('.process-step.completed').length;
  const activeStep = document.querySelector('.process-step.active') ? 1 : 0;
  const progress = ((completedSteps + (activeStep * 0.5)) / totalSteps) * 100;
  
  const stepProgressBar = document.getElementById('stepProgressBar');
  if (stepProgressBar) {
    stepProgressBar.style.width = `${progress}%`;
  }
}

/**
 * Map progress percentage to step number and update the UI
 * @param {Number} percent - Overall progress percentage (0-100)
 * @param {String} status - Status message that helps determine the step
 */
function mapProgressToStep(percent, status) {
  // Parse the status message to determine the step more accurately
  let step = 1; // Default to step 1 (Preparation)
  let completed = false;
  
  // Step detection logic based on progress percentage and status message
  if (percent >= 95) {
    step = 4; // Completion step
    completed = true;
  } else if (percent >= 50) {
    step = 3; // Document processing step
    completed = false;
  } else if (percent >= 15 || 
            (status && (status.includes('tangkapan layar') || 
                      status.includes('screenshot') || 
                      status.includes('patroli')))) {
    step = 2; // Screenshot step
    completed = percent >= 30;
  }
  
  // Special cases from status messages
  if (status) {
    if (status.includes('Word') || status.includes('PDF') || status.includes('Dokumen')) {
      step = 3;
      completed = status.includes('selesai');
    } else if (status.includes('Selesai') || status.includes('Menampilkan hasil')) {
      step = 4;
      completed = true;
    }
  }
  
  updateProcessStep(step, completed);
  
  // Add detailed step information to the debug log
  if (typeof debugLog === 'function') {
    debugLog('Progress mapped to step', { 
      percent: percent, 
      status: status, 
      mappedStep: step, 
      completed: completed 
    });
  }
}
