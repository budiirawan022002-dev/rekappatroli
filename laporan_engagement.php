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

    <div class="row g-3 g-md-4 justify-content-center">
      <div class="col-12 col-xl-10">
        <div class="card rounded-4 shadow-lg border-0" style="background: #ffffff; border: 1px solid #cfe2ff !important;">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-chat-heart-fill me-2"></i>Form Laporan Engagement</h5>
          </div>
          <div class="card-body p-3">
            <form id="engagementForm" enctype="multipart/form-data">
              <!-- Tanggal dan Judul -->
              <div class="mb-3">
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

              <!-- Input Data Engagement -->
              <div class="mb-3">
                <label for="linksInput" class="form-label"><i class="bi bi-link-45deg"></i> Input Data Engagement (format keyword) <span class="text-danger">*</span></label>
                <textarea class="form-control" id="linksInput" name="linksInput" rows="7" placeholder="Keyword: kualitas layanan MBG&#10;@beritasatset https://www.tiktok.com/@beritasatset/video/7626622458581224725&#10;@POSITIFITI https://x.com/POSITIFITI/status/2042109002147135525&#10;&#10;Keyword: jaga stabilitas rupiah&#10;@infonusa https://www.tiktok.com/@infonusa/video/7626622539762027797&#10;@amanbangetkok https://x.com/amanbangetkok/status/2042109049542734083" required></textarea>
                <small class="text-muted">Gunakan format: <code>Keyword:</code> lalu daftar <code>@akun link</code></small>
              </div>

              <div class="mb-3">
                <label for="komentarInput" class="form-label"><i class="bi bi-chat-left-text"></i> Input Komentar (format: jumlah | isi komentar | akun) <span class="text-danger">*</span></label>
                <textarea class="form-control" id="komentarInput" name="komentarInput" rows="6" placeholder="Keyword: kualitas layanan MBG&#10;5 | indonesia jaya | a&#10;&#10;Keyword: jaga stabilitas rupiah&#10;3 | indonesia jaya | a" required></textarea>
                <small class="text-muted">Angka komentar dihitung per <code>Keyword</code>. Jika tanpa keyword, sistem pakai mode umum (baris biasa).</small>
              </div>

              <!-- Dynamic Forms per Link -->
              <div id="linkFormsContainer"></div>

              <!-- Preview -->
              <div class="mb-3">
                <div class="card shadow-none border">
                  <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-eye"></i> Preview</h6>
                  </div>
                  <div class="card-body">
                    <textarea id="previewOutput" class="form-control result-textarea" rows="8" readonly></textarea>
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

@media (min-width: 1600px) {
  .main-content .col-xl-10 {
    max-width: 1200px;
  }
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
  if (url.includes('twitter.com') || url.includes('x.com')) return 'X/Twitter';
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

function parseEngagementInput(rawInput) {
  const lines = (rawInput || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
  const parsedItems = [];
  let currentKeyword = '';

  lines.forEach(rawLine => {
    const line = rawLine.trim();
    if (!line) return;

    const keywordMatch = line.match(/^keyword\s*:\s*(.+)$/i);
    if (keywordMatch) {
      currentKeyword = keywordMatch[1].trim();
      return;
    }

    const accountMatch = line.match(/^(@[^\s]+)\s+(https?:\/\/[^\s]+)$/i);
    if (accountMatch) {
      const akun = accountMatch[1].trim();
      const url = accountMatch[2].trim();
      parsedItems.push({
        akun,
        url,
        keyword: currentKeyword || 'tanpa keyword',
        keywordKey: normalizeKeyword(currentKeyword || 'tanpa keyword'),
        platform: detectPlatform(url)
      });
    }
  });

  return parsedItems;
}

function normalizeKeyword(keyword) {
  return String(keyword || '')
    .toLowerCase()
    .replace(/\s+/g, ' ')
    .trim();
}

function parseKomentarInput(rawInput) {
  const lines = (rawInput || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
  const entries = [];
  const byKeyword = {};
  let total = 0;
  let currentKeyword = '';

  lines.forEach(rawLine => {
    const line = rawLine.trim();
    if (!line) return;

    const keywordMatch = line.match(/^keyword\s*:\s*(.+)$/i);
    if (keywordMatch) {
      currentKeyword = keywordMatch[1].trim();
      return;
    }

    const parts = line.split('|').map(p => p.trim());
    if (parts.length < 2) return;

    // Support 2 formats:
    // 1) qty | komentar | akun
    // 2) komentar | akun | totalTema
    let qty = 0;
    let text = '';
    let akun = '';
    let isTotalTema = false;

    const firstNum = parseInt(parts[0], 10);
    const lastNum = parseInt(parts[parts.length - 1], 10);

    if (!Number.isNaN(firstNum)) {
      qty = firstNum;
      text = parts[1] || '';
      akun = parts[2] || '';
    } else if (!Number.isNaN(lastNum)) {
      qty = lastNum;
      text = parts[0] || '';
      akun = parts[1] || '';
      isTotalTema = true;
    } else {
      // Fallback: keep safe behavior
      qty = 1;
      text = parts[0] || '';
      akun = parts[1] || '';
    }

    const safeQty = qty > 0 ? qty : 0;
    total += safeQty;
    const parsed = { qty: safeQty, text, akun, isTotalTema };
    entries.push(parsed);

    if (currentKeyword) {
      const key = normalizeKeyword(currentKeyword);
      if (!byKeyword[key]) {
        byKeyword[key] = { qty: 0, text: parsed.text, akun: parsed.akun, isTotalTema: parsed.isTotalTema };
      }
      byKeyword[key].qty += parsed.qty;
      if (parsed.text) byKeyword[key].text = parsed.text;
      if (parsed.akun) byKeyword[key].akun = parsed.akun;
      if (parsed.isTotalTema) byKeyword[key].isTotalTema = true;
    }
  });

  return { entries, byKeyword, total };
}

function getKomentarQtyForItem(item, itemIndex, allItems, komentarData) {
  const key = item.keywordKey || normalizeKeyword(item.keyword || '');
  const hasKeywordMap = Boolean(komentarData.byKeyword[key]);
  const mapped = komentarData.byKeyword[key] || komentarData.entries[itemIndex % komentarData.entries.length];
  if (!mapped) {
    const totalGlobal = Number(komentarData.total || 0);
    if (totalGlobal > 0) {
      const countAll = allItems.length || 1;
      const baseAll = Math.floor(totalGlobal / countAll);
      const remAll = totalGlobal % countAll;
      return baseAll + (itemIndex < remAll ? 1 : 0);
    }
    return 1;
  }

  let qty = (mapped.qty && mapped.qty > 0) ? mapped.qty : 1;

  // For keyword-mapped data, qty is treated as TOTAL per tema and distributed.
  // This supports both:
  // - "komentar | akun | 30"
  // - repeated lines like "1 | komentar | akun" under the same keyword.
  if (hasKeywordMap || mapped.isTotalTema) {
    const itemsInKeyword = allItems.filter(x => (x.keywordKey || normalizeKeyword(x.keyword || '')) === key);
    const countInKeyword = itemsInKeyword.length || 1;

    const base = Math.floor(qty / countInKeyword);
    const remainder = qty % countInKeyword;

    const currentPos = itemsInKeyword.findIndex(x => x.url === item.url && x.akun === item.akun);
    const safePos = currentPos >= 0 ? currentPos : 0;
    return base + (safePos < remainder ? 1 : 0);
  }

  // Fallback when keyword mapping is not available:
  // use global total and distribute across all links.
  const totalGlobal = Number(komentarData.total || 0);
  if (totalGlobal > 0) {
    const countAll = allItems.length || 1;
    const baseAll = Math.floor(totalGlobal / countAll);
    const remAll = totalGlobal % countAll;
    return baseAll + (itemIndex < remAll ? 1 : 0);
  }

  return qty;
}

// Generate ringkasan dari input keyword
function generateForms() {
  const container = document.getElementById('linkFormsContainer');
  const linksInput = document.getElementById('linksInput').value.trim();
  
  if (!linksInput) {
    container.innerHTML = '';
    links = [];
    updatePreview();
    return;
  }

  const parsedItems = parseEngagementInput(linksInput);
  links = parsedItems.map((item, index) => ({
    url: item.url,
    platform: item.platform,
    index: index
  }));

  if (parsedItems.length === 0) {
    container.innerHTML = `<div class="alert alert-warning py-2 px-3">Format belum valid. Gunakan <b>Keyword:</b> lalu baris <b>@akun link</b>.</div>`;
    updatePreview();
    return;
  }

  container.innerHTML = `
    <div class="alert alert-info py-2 px-3 mb-2">
      Total data terbaca: <b>${parsedItems.length}</b> akun/link.
    </div>
    ${parsedItems.map((item, idx) => `
      <div class="link-form-section py-2 px-3 mb-2">
        <small><b>${idx + 1}.</b> ${item.platform} ${item.akun}<br>${item.url}<br><b>Keyword:</b> ${item.keyword}</small>
      </div>
    `).join('')}
  `;

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

  const data = parseEngagementInput(document.getElementById('linksInput').value.trim());
  const komentar = parseKomentarInput(document.getElementById('komentarInput').value.trim());

  if (data.length === 0) {
    previewOutput.value = 'Masukkan data dengan format Keyword + @akun link terlebih dahulu';
    return;
  }
  if (komentar.entries.length === 0) {
    previewOutput.value = 'Masukkan komentar dengan format: jumlah | isi komentar | akun';
    return;
  }

  const tanggalFormatted = formatTanggalIndonesia(tanggal);
  const totalKomentar = komentar.total;

  // Format preview
  let preview = `Kepada Yth.: Kasuari-6\n\n`;
  preview += `Tembusan :\n`;
  preview += `1. Kasuari-21\n2. Kasuari-22\n3. Kasuari-23\n4. Kasuari-24\n5. Kasuari-25\n6. Kasuari-63\n\n`;
  preview += `Dari : Merpati-14\n\n`;
  preview += `Perihal : Upaya Pembanjiran Komentar Terhadap Konten Positif terkait isu ${judul}, Periode ${tanggalFormatted}\n\n`;
  preview += `Izin melaporkan pada ${tanggalFormatted}, Merpati-14 telah melakukan upaya pembanjiran komentar terhadap konten Positif terkait isu ${judul} dengan total ${totalKomentar} komentar. Adapun rincian kegiatan sebagai berikut:\n\n`;

  data.forEach((item, idx) => {
    const jumlahKomentar = getKomentarQtyForItem(item, idx, data, komentar);
    preview += `${idx + 1}. Akun ${item.platform} ${item.akun} ${item.url} Komen: ${jumlahKomentar} komentar\n\n`;
  });

  preview += `Selanjutnya lampiran pelaksanaan telah terkirim pada google form.\n\nDUMP`;
  previewOutput.value = preview.trim();
}

function formatTanggalIndonesia(tanggal) {
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  const d = new Date(tanggal);
  if (Number.isNaN(d.getTime())) return tanggal;
  return `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

// Save form data to localStorage
function saveFormData() {
  if (isLoadingData) return;
  
  const data = {
    tanggal: document.getElementById('tanggal').value,
    judul: document.getElementById('judul').value,
    linksInput: document.getElementById('linksInput').value,
    komentarInput: document.getElementById('komentarInput').value,
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
      if (data.komentarInput) document.getElementById('komentarInput').value = data.komentarInput;
      
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
  const komentarInput = document.getElementById('komentarInput').value.trim();
  
  if (!tanggal || !judul || !linksInput || !komentarInput) {
    alert('Mohon lengkapi semua field yang wajib diisi!');
    return;
  }

  // Validate links
  const parsedItems = parseEngagementInput(linksInput);
  if (parsedItems.length === 0) {
    alert('Format input tidak valid. Gunakan format: Keyword lalu @akun link');
    return;
  }
  const komentarParsed = parseKomentarInput(komentarInput);
  if (komentarParsed.entries.length === 0) {
    alert('Format komentar tidak valid. Gunakan: jumlah | isi komentar | akun');
    return;
  }

  const links = parsedItems.map(item => item.url);
  const platforms = parsedItems.map(item => item.platform);
  const namaAkun = parsedItems.map(item => item.akun);
  const narasi = parsedItems.map((item, idx) => {
    const jumlahKomentar = getKomentarQtyForItem(item, idx, parsedItems, komentarParsed);
    return `${jumlahKomentar} komentar`;
  });
  const linkIndexes = parsedItems.map((_, idx) => idx);

  if (namaAkun.length === 0) {
    alert('Minimal harus ada satu akun dengan narasi!');
    return;
  }

  // Build FormData
  formData.append('action', 'generate_engagement_report');
  formData.append('tanggal', tanggal);
  formData.append('judul', judul);
  formData.append('komentarTotal', String(komentarParsed.total));
  
  namaAkun.forEach(akun => formData.append('namaAkun[]', akun));
  narasi.forEach(nar => formData.append('narasi[]', nar));
  links.forEach(link => formData.append('links[]', link));
  platforms.forEach(plat => formData.append('platforms[]', plat));
  linkIndexes.forEach(idx => formData.append('linkIndexes[]', idx));

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
document.getElementById('komentarInput').addEventListener('input', () => {
  if (!isLoadingData) {
    saveFormData();
    updatePreview();
  }
});

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
  loadFormData();
});
</script>

<?php include_once('includes/footer.php'); ?>
