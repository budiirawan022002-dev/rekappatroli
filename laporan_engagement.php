<?php
require_once('auth_check.php');
include_once('includes/header.php');
?>

<main class="main-wrapper">
  <div class="main-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4 shadow-sm">
      <div class="breadcrumb-title pe-3">
        <h4 class="mb-0 fw-bold"><i class="bi bi-chat-heart-fill me-2"></i>Laporan Engagement</h4>
      </div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Laporan Engagement</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row g-3 g-md-4">
      <div class="col-12">
        <div class="card rounded-4 shadow-lg border-0" style="background: #ffffff; border: 1px solid #cfe2ff !important;">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-chat-heart-fill me-2"></i>Form Laporan Engagement</h5>
          </div>
          <div class="card-body p-3 p-md-4">
            <form id="engagementForm" enctype="multipart/form-data">
              <!-- Tanggal dan Judul -->
              <div class="mb-4">
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label for="tanggal" class="form-label"><i class="bi bi-calendar-event"></i> Tanggal Laporan <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                  </div>
                  <div class="col-12 col-md-6">
                    <label for="judul" class="form-label"><i class="bi bi-file-text"></i> Judul Laporan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul" placeholder="Contoh: Engagement Terhadap Konten Negatif" required>
                  </div>
                </div>
              </div>

              <!-- Input Links -->
              <div class="mb-4">
                <label for="linksInput" class="form-label"><i class="bi bi-link-45deg"></i> Link Konten (satu per baris) <span class="text-danger">*</span></label>
                <textarea class="form-control" id="linksInput" name="linksInput" rows="5" placeholder="Masukkan link konten, satu per baris&#10;Contoh:&#10;https://www.instagram.com/p/xxx/&#10;https://vt.tiktok.com/xxx/" required></textarea>
                <small class="text-muted">Masukkan link konten yang akan di-engagement, satu per baris</small>
              </div>

              <!-- Dynamic Forms per Link -->
              <div id="linkFormsContainer"></div>

              <!-- Preview -->
              <div class="mb-4">
                <div class="card shadow-none border">
                  <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-eye"></i> Preview</h6>
                  </div>
                  <div class="card-body">
                    <textarea id="previewOutput" class="form-control result-textarea" rows="10" readonly></textarea>
                  </div>
                </div>
              </div>

              <!-- Buttons -->
              <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-file-earmark-check me-2"></i>Generate Laporan
                </button>
                <button type="button" class="btn btn-secondary" onclick="clearFormData()">
                  <i class="bi bi-trash me-2"></i>Hapus Semua Data
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<style>
.evidence-preview-card {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 8px;
  margin-bottom: 8px;
  background: #f8f9fa;
}

.evidence-image-4x6 {
  width: 100%;
  max-width: 200px;
  aspect-ratio: 2/3;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid #dee2e6;
}

.link-form-section {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
  background: #f8f9fa;
}

.link-form-section h6 {
  color: #0d6efd;
  margin-bottom: 10px;
}
</style>

<script>
let links = [];
let isLoadingData = false;

// Deteksi platform dari URL
function detectPlatform(url) {
  if (!url) return 'Unknown';
  url = url.toLowerCase();
  if (url.includes('instagram.com')) return 'Instagram';
  if (url.includes('facebook.com')) return 'Facebook';
  if (url.includes('tiktok.com')) return 'TikTok';
  if (url.includes('twitter.com') || url.includes('x.com')) return 'Twitter/X';
  if (url.includes('youtube.com')) return 'YouTube';
  return 'Unknown';
}

// Validasi URL
function isValidUrl(string) {
  try {
    const url = new URL(string);
    return url.protocol === 'http:' || url.protocol === 'https:';
  } catch (_) {
    return false;
  }
}

// Generate forms untuk setiap link
function generateForms() {
  const container = document.getElementById('linkFormsContainer');
  const linksInput = document.getElementById('linksInput').value.trim();
  
  if (!linksInput) {
    container.innerHTML = '';
    links = [];
    updatePreview();
    return;
  }

  const newLinks = linksInput.split('\n').map(l => l.trim()).filter(l => l && isValidUrl(l));
  
  // Hanya update jika links berubah
  if (JSON.stringify(newLinks) === JSON.stringify(links.map(l => l.url))) {
    return;
  }

  links = newLinks.map((url, index) => ({
    url: url,
    platform: detectPlatform(url),
    index: index
  }));

  container.innerHTML = links.map(link => `
    <div class="link-form-section" data-link-index="${link.index}">
      <h6><i class="bi bi-link-45deg"></i> Link ${link.index + 1}: ${link.platform}</h6>
      <div class="mb-2">
        <small class="text-muted d-block mb-2">${link.url}</small>
      </div>
      <div class="mb-3">
        <label class="form-label"><i class="bi bi-person"></i> Nama Akun dan Narasi (satu per baris, format: @akun | narasi)</label>
        <textarea class="form-control" id="accounts_link_${link.index}" name="accounts_link_${link.index}" rows="4" placeholder="@akun1 | Narasi komentar 1&#10;@akun2 | Narasi komentar 2"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label"><i class="bi bi-image"></i> Upload Evidence (Foto bukti engagement)</label>
        <input type="file" class="form-control" id="evidence_link_${link.index}" name="evidence_link_${link.index}[]" accept="image/*" multiple>
        <div id="evidence_preview_${link.index}" class="mt-2"></div>
      </div>
    </div>
  `).join('');

  // Attach event listeners
  links.forEach(link => {
    const accountsInput = document.getElementById(`accounts_link_${link.index}`);
    const evidenceInput = document.getElementById(`evidence_link_${link.index}`);
    
    if (accountsInput) {
      accountsInput.addEventListener('input', () => {
        if (!isLoadingData) {
          saveFormData();
          updatePreview();
        }
      });
    }
    
    if (evidenceInput) {
      evidenceInput.addEventListener('change', (e) => {
        showEvidencePreview(link.index, e.target.files);
        if (!isLoadingData) {
          saveFormData();
        }
      });
    }
  });

  loadFormData();
  updatePreview();
}

// Show evidence preview
function showEvidencePreview(linkIndex, files) {
  const previewContainer = document.getElementById(`evidence_preview_${linkIndex}`);
  if (!previewContainer || !files || files.length === 0) {
    if (previewContainer) previewContainer.innerHTML = '';
    return;
  }

  previewContainer.innerHTML = Array.from(files).map((file, index) => `
    <div class="evidence-preview-card">
      <img src="${URL.createObjectURL(file)}" alt="Preview ${index + 1}" class="evidence-image-4x6">
      <small class="d-block mt-1">${file.name}</small>
    </div>
  `).join('');
}

// Update preview
function updatePreview() {
  const previewOutput = document.getElementById('previewOutput');
  if (!previewOutput) return;

  const tanggal = document.getElementById('tanggal').value;
  const judul = document.getElementById('judul').value;

  if (!tanggal || !judul) {
    previewOutput.value = 'Isi tanggal dan judul terlebih dahulu';
    return;
  }

  // Collect data
  const data = [];
  links.forEach(link => {
    const accountsInput = document.getElementById(`accounts_link_${link.index}`);
    if (accountsInput && accountsInput.value.trim()) {
      const accounts = accountsInput.value.trim().split('\n').filter(a => a.trim());
      data.push({
        link: link.url,
        platform: link.platform,
        accounts: accounts
      });
    }
  });

  if (data.length === 0) {
    previewOutput.value = 'Masukkan link dan data akun terlebih dahulu';
    return;
  }

  // Format preview
  let preview = `Kepada Yth.: Kasuari-2\n`;
  preview += `Dari : Merpati-14\n`;
  preview += `Tembusan :\n`;
  preview += `1. Kasuari-21\n2. Kasuari-22\n3. Kasuari-23\n4. Kasuari-24\n5. Kasuari-25\n6. Kasuari-63\n7. Kasuari-75\n\n`;
  preview += `Perihal : ${judul}\n\n`;
  preview += `A. EXECUTIVE SUMMARY\n`;
  preview += `Pada ${tanggal}, telah dilakukan upaya ${judul} dengan total X akun.\n\n`;
  preview += `B. HASIL ENGAGEMENT\n`;

  data.forEach((item, idx) => {
    preview += `${idx + 1}. ${item.link}\n`;
    item.accounts.forEach((account, accIdx) => {
      const parts = account.split('|').map(p => p.trim());
      const akun = parts[0] || '';
      const narasi = parts[1] || '';
      const letter = String.fromCharCode(97 + accIdx); // a, b, c, ...
      preview += `${letter}. Melakukan like dan komen menggunakan akun ${akun} dengan narasi "${narasi}"\n`;
    });
    if (idx < data.length - 1) preview += '\n';
  });

  previewOutput.value = preview;
}

// Save form data to localStorage
function saveFormData() {
  if (isLoadingData) return;
  
  const data = {
    tanggal: document.getElementById('tanggal').value,
    judul: document.getElementById('judul').value,
    linksInput: document.getElementById('linksInput').value,
    links: []
  };

  links.forEach(link => {
    const accountsInput = document.getElementById(`accounts_link_${link.index}`);
    if (accountsInput) {
      data.links.push({
        index: link.index,
        accounts: accountsInput.value
      });
    }
  });

  localStorage.setItem('engagementFormData', JSON.stringify(data));
}

// Load form data from localStorage
function loadFormData() {
  isLoadingData = true;
  const saved = localStorage.getItem('engagementFormData');
  
  if (saved) {
    try {
      const data = JSON.parse(saved);
      if (data.tanggal) document.getElementById('tanggal').value = data.tanggal;
      if (data.judul) document.getElementById('judul').value = data.judul;
      if (data.linksInput) document.getElementById('linksInput').value = data.linksInput;
      
      // Generate forms first
      generateForms();
      
      // Then load accounts data
      setTimeout(() => {
        if (data.links) {
          data.links.forEach(linkData => {
            const accountsInput = document.getElementById(`accounts_link_${linkData.index}`);
            if (accountsInput) {
              accountsInput.value = linkData.accounts || '';
            }
          });
        }
        updatePreview();
        isLoadingData = false;
      }, 100);
    } catch (e) {
      console.error('Error loading form data:', e);
      isLoadingData = false;
    }
  } else {
    isLoadingData = false;
  }
}

// Clear form data
function clearFormData() {
  if (confirm('Yakin ingin menghapus semua data?')) {
    localStorage.removeItem('engagementFormData');
    document.getElementById('engagementForm').reset();
    document.getElementById('linkFormsContainer').innerHTML = '';
    document.getElementById('previewOutput').value = '';
    links = [];
  }
}

// Form submission
document.getElementById('engagementForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const tanggal = document.getElementById('tanggal').value;
  const judul = document.getElementById('judul').value;
  const linksInput = document.getElementById('linksInput').value.trim();
  
  if (!tanggal || !judul || !linksInput) {
    alert('Mohon lengkapi semua field yang wajib diisi!');
    return;
  }

  // Validate links
  const linksArray = linksInput.split('\n').map(l => l.trim()).filter(l => l);
  const validLinks = linksArray.filter(l => isValidUrl(l));
  
  if (validLinks.length === 0) {
    alert('Minimal harus ada satu link yang valid!');
    return;
  }

  // Collect all data
  const links = [];
  const platforms = [];
  const namaAkun = [];
  const narasi = [];
  const linkIndexes = [];
  
  validLinks.forEach((url, linkIdx) => {
    const platform = detectPlatform(url);
    const accountsInput = document.getElementById(`accounts_link_${linkIdx}`);
    
    if (accountsInput && accountsInput.value.trim()) {
      const accounts = accountsInput.value.trim().split('\n').filter(a => a.trim());
      
      accounts.forEach(account => {
        const parts = account.split('|').map(p => p.trim());
        const akun = parts[0] || '';
        const narasiText = parts[1] || '';
        
        if (akun) {
          links.push(url);
          platforms.push(platform);
          namaAkun.push(akun);
          narasi.push(narasiText);
          linkIndexes.push(linkIdx);
        }
      });
    }
  });

  if (namaAkun.length === 0) {
    alert('Minimal harus ada satu akun dengan narasi!');
    return;
  }

  // Build FormData
  formData.append('action', 'generate_engagement_report');
  formData.append('tanggal', tanggal);
  formData.append('judul', judul);
  
  namaAkun.forEach(akun => formData.append('namaAkun[]', akun));
  narasi.forEach(nar => formData.append('narasi[]', nar));
  links.forEach(link => formData.append('links[]', link));
  platforms.forEach(plat => formData.append('platforms[]', plat));
  linkIndexes.forEach(idx => formData.append('linkIndexes[]', idx));

  // Add evidence files
  validLinks.forEach((url, linkIdx) => {
    const evidenceInput = document.getElementById(`evidence_link_${linkIdx}`);
    if (evidenceInput && evidenceInput.files.length > 0) {
      Array.from(evidenceInput.files).forEach((file, fileIdx) => {
        formData.append(`evidence_link_${linkIdx}[]`, file);
      });
    }
  });

  // Show loading
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses...';

  try {
    const response = await fetch('api_engagement.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      // Show result
      const resultModal = document.createElement('div');
      resultModal.className = 'modal fade show';
      resultModal.style.display = 'block';
      resultModal.innerHTML = `
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Berhasil!</h5>
              <button type="button" class="btn-close btn-close-white" onclick="this.closest('.modal').remove()"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label"><strong>Format WhatsApp:</strong></label>
                <textarea class="form-control result-textarea" rows="10" readonly>${result.whatsapp_format || ''}</textarea>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="copyToClipboard(this.previousElementSibling)"><i class="bi bi-clipboard me-1"></i>Copy</button>
              </div>
              ${result.file_path ? `
              <div class="mb-3">
                <label class="form-label"><strong>File Word:</strong></label>
                <div>
                  <a href="${result.file_path}" class="btn btn-primary" download><i class="bi bi-download me-2"></i>Download Word Document</a>
                </div>
              </div>
              ` : ''}
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Tutup</button>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(resultModal);
      
      // Clear form data on success
      clearFormData();
    } else {
      alert('Error: ' + (result.message || 'Terjadi kesalahan'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Terjadi kesalahan: ' + error.message);
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
  }
});

// Copy to clipboard
function copyToClipboard(element) {
  element.select();
  document.execCommand('copy');
  alert('Copied to clipboard!');
}

// Event listeners
document.getElementById('tanggal').addEventListener('change', () => {
  if (!isLoadingData) {
    saveFormData();
    updatePreview();
  }
});

document.getElementById('judul').addEventListener('input', () => {
  if (!isLoadingData) {
    saveFormData();
    updatePreview();
  }
});

document.getElementById('linksInput').addEventListener('input', () => {
  if (!isLoadingData) {
    saveFormData();
    generateForms();
  }
});

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
  loadFormData();
});
</script>

<?php include_once('includes/footer.php'); ?>
